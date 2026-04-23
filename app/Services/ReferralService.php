<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Referral;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Settings\ReferralSettings;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    /**
     * Get Standard Commission Rate
     */
    private static function getApplicableRate(User $referrer, ReferralSettings $settings): float
    {
        $custom = $referrer->referralSetting;

        return ! is_null($custom?->commission_percentage)
            ? (float) $custom->commission_percentage
            : $settings->commission_percentage;
    }

    /**
     * Handle Referral Commission
     */
    public static function handleReferral(User $user, Payment $payment)
    {
        try {
            Log::info("Referral Process Started: User {$user->id}, Payment {$payment->id}, Amount {$payment->total_amount}");

            $settings = app(ReferralSettings::class);

            // 1. Basic Validation
            if (! $settings->enable_referral || $payment->total_amount <= 0) {
                Log::info('Referral Skipped: System disabled or Amount 0');

                return;
            }

            // 2. DUPLICATE CHECK (Strict Payment ID Check)
            if (Referral::where('payment_id', $payment->id)->exists()) {
                Log::info("Referral Skipped: Payment {$payment->id} already processed.");

                return;
            }

            // 3. IDENTIFY REFERRER
            $referrer = null;
            $referralCode = Cookie::get('referral_code');

            // Priority 1: Browser Cookie
            if ($referralCode) {
                $referrer = User::where('referral_code', $referralCode)->first();
            }

            // Priority 2: Database History (Permanent Association)
            // Agar cookie nahi hai, to check karo user pehle kis se juda tha.
            // if (!$referrer) {
            //     // Get the very first approved referral to find original referrer
            //     // OR Get the latest approved referral (depending on business logic).
            //     // Usually "First Referrer Attribution" is standard, but here let's stick to 'Latest Successful'
            //     $lastReferral = Referral::where('referee_id', $user->id)
            //                             ->where('status', 'approved')
            //                             ->latest()
            //                             ->first();

            //     if ($lastReferral) {
            //         $referrer = User::find($lastReferral->referrer_id);
            //         if ($referrer) {
            //             Log::info("Referral: Cookie missing. Found existing relationship with Referrer ID {$referrer->id}");
            //         }
            //     }
            // }

            // 4. VALIDATE REFERRER
            if (! $referrer || $referrer->id === $user->id) {
                Log::info('Referral Skipped: No valid referrer found (or self-referral).');

                return;
            }

            // --- EXECUTION ---
            DB::transaction(function () use ($settings, $referrer, $user, $payment) {

                // Lock row to prevent race conditions on wallet balance
                /** @var \App\Models\User $referrerLocked */
                $referrerLocked = User::with('referralSetting')
                    ->lockForUpdate()
                    ->find($referrer->id);

                if (! $referrerLocked) {
                    return;
                }

                // Calculate Commission
                $commissionRate = self::getApplicableRate($referrerLocked, $settings);

                if ($commissionRate <= 0) {
                    Log::info('Referral Skipped: Commission rate is 0.');

                    return;
                }

                // Use sub_total (Base Price) for commission if available, else fallback to total_amount
                // This ensures we don't pay commission on the GST/Tax portion.
                $baseAmount = (float) ($payment->data->get('order_summary.sub_total') ?? $payment->total_amount);

                $commissionAmount = ($baseAmount * $commissionRate) / 100;

                // 5. Create Referral Record
                Referral::create([
                    'referrer_id' => $referrerLocked->id,
                    'referee_id' => $user->id,
                    'payment_id' => $payment->id,
                    'plan_amount' => $baseAmount,
                    'commission_percentage' => $commissionRate,
                    'commission_amount' => $commissionAmount,
                    'status' => 'approved',
                ]);

                // 6. Update Wallet
                $studentName = $user->full_name ?? $user->name ?? 'Student';
                $planName = $payment->plan ? $payment->plan->name : 'Subscription';

                $desc = "Referral Reward ({$commissionRate}%): {$planName} purchased by {$studentName}";

                $newRunningBalance = $referrerLocked->wallet_balance + $commissionAmount;

                WalletTransaction::create([
                    'user_id' => $referrerLocked->id,
                    'amount' => $commissionAmount,
                    'type' => 'credit',
                    'source' => 'referral_reward',
                    'description' => $desc,
                    'reference_id' => $payment->id,
                    'running_balance' => $newRunningBalance,
                ]);

                $referrerLocked->wallet_balance = $newRunningBalance;
                $referrerLocked->save();

                Log::info("Referral Success: Commission {$commissionAmount} added to Referrer {$referrerLocked->id}");
            });
        } catch (\Exception $e) {
            Log::error('Referral Critical Error: ' . $e->getMessage());
        }
    }

    /**
     * Disable revisit reward
     */
    public static function handleRevisitReward(User $user, Plan $plan) {}
}
