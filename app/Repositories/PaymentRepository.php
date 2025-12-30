<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Payment;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentRepository
{
    public function createPaymentAndSubscription(array $data): ?Payment
    {
        return DB::transaction(function () use ($data) {
            try {
                // 1. Create Payment Record
                $payment = new Payment();
                $payment->payment_id = $data['payment_id'];
                $payment->currency = $data['currency'];
                $payment->plan_id = $data['plan_id'];
                $payment->user_id = $data['user_id'];
                $payment->total_amount = $data['total_amount'];
                $payment->payment_processor = 'razorpay';
                $payment->status = $data['status']; // 'pending' or 'success'
                $payment->data = $data['meta_data'] ?? [];

                // If success, update dates and transaction ID
                if ($data['status'] === 'success') {
                    $payment->payment_date = Carbon::now();
                    $payment->transaction_id = $data['transaction_id'] ?? null;
                }

                $payment->save();

                // 2. If Success, Create Subscription
                if ($data['status'] === 'success') {
                    $subscription = new Subscription();
                    $subscription->user_id = $data['user_id'];
                    $subscription->plan_id = $data['plan_id'];
                    $subscription->payment_id = $payment->id;
                    $subscription->starts_at = Carbon::now();
                    $subscription->ends_at = Carbon::now()->addMonths((int) $data['duration']);
                    $subscription->status = 'active';
                    $subscription->save();
                }

                return $payment;

            } catch (\Exception $e) {
                Log::error("DB Transaction Failed: " . $e->getMessage());
                throw $e; // Re-throw to rollback transaction
            }
        });
    }
}
