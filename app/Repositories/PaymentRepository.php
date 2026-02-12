<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Payment;
use App\Models\Subscription;
use App\Settings\BillingSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentRepository
{
    public function __construct(
        protected BillingSettings $billingSettings
    ) {}

    /**
     * Create Initial Payment Record (Pending Status)
     * Merged Old Logic: Adds Invoice ID & Billing Info
     */
    public function createPayment(array $data): ?Payment
    {
        $payment = new Payment();
        $payment->payment_id = $data['payment_id'];
        $payment->currency = $data['currency'];
        $payment->plan_id = $data['plan_id'];
        $payment->user_id = $data['user_id'];
        $payment->total_amount = $data['total_amount'];
        $payment->payment_processor = $data['payment_processor'];
        $payment->status = $data['status']; // usually 'pending'

        // --- MERGED LOGIC: Save Billing Info for Invoice ---
        // Hum purane structure ko follow kar rahe hain taaki Invoice PDF na toote
        $payment->data = [
            'vendor_billing_information'   => $this->billingSettings->toArray(),
            'customer_billing_information' => $data['meta_data']['billing_info'] ?? [],
            'order_summary'                => $data['meta_data']['order_summary'] ?? [],
            'razorpay_order_id'            => $data['meta_data']['razorpay_order_id'] ?? null,
        ];

        $payment->save();

        // --- MERGED LOGIC: Generate Invoice ID ---
        // Format: INV-00001
        $payment->invoice_id = $this->billingSettings->invoice_prefix . '-' . Str::padLeft((string)$payment->id, 5, '0');
        $payment->save(); // Update with Invoice ID

        return $payment;
    }

    /**
     * Confirm Payment & Activate Subscription (Called after Razorpay Callback)
     */
    public function confirmPayment(Payment $payment, string $transactionId): void
    {
        DB::transaction(function () use ($payment, $transactionId) {
            // 1. Update Payment Status
            $payment->transaction_id = $transactionId;
            $payment->payment_date = Carbon::now();
            $payment->status = 'success';
            $payment->save();

            // 2. Create Active Subscription (If not exists)
            $this->createSubscription([
                'user_id' => $payment->user_id,
                'plan_id' => $payment->plan_id,
                'payment_id' => $payment->id,
                'category_type' => $payment->plan->category_type ?? null,
                'category_id' => $payment->plan->category_id ?? null,
                'duration' => $payment->plan->duration ?? 12,
                'status' => 'active'
            ]);
        });
    }

    /**
     * Handle One-Shot creation (For Free Plans)
     */
    public function createPaymentAndSubscription(array $data): ?Payment
    {
        return DB::transaction(function () use ($data) {
            // Use the main create method to ensure Invoice ID is generated
            $payment = $this->createPayment($data);

            if ($data['status'] === 'success') {
                // Manually set transaction ID for free plans if provided
                if (isset($data['transaction_id'])) {
                    $payment->transaction_id = $data['transaction_id'];
                    $payment->payment_date = Carbon::now();
                    $payment->save();
                }

                // Fetch Plan to get details if not in data
                $plan = $payment->plan;

                $this->createSubscription([
                    'user_id' => $data['user_id'],
                    'plan_id' => $data['plan_id'],
                    'payment_id' => $payment->id,
                    'category_type' => $plan->category_type ?? null,
                    'category_id' => $plan->category_id ?? null,
                    'duration' => $plan->duration ?? 12,
                    'status' => 'active'
                ]);
            }

            return $payment;
        });
    }

    /**
     * Helper to create subscription model (Cleaned up)
     */
    private function createSubscription(array $data): Subscription
    {
        $subscription = new Subscription();
        $subscription->user_id = $data['user_id'];
        $subscription->plan_id = $data['plan_id'];
        $subscription->payment_id = $data['payment_id'];
        $subscription->category_type = $data['category_type'];
        $subscription->category_id = $data['category_id'];

        $subscription->starts_at = Carbon::now();
        $subscription->ends_at = Carbon::now()->addMonths((int) $data['duration']);
        $subscription->status = $data['status'];

        $subscription->save();

        return $subscription;
    }
}
