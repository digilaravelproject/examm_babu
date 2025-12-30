<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Settings\PaymentSettings;
use App\Settings\RazorpaySettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayRepository
{
    public function __construct(protected RazorpaySettings $settings) {}

    public function createOrder(string $paymentId, float $amount): ?array
    {
        try {
            $response = Http::withBasicAuth($this->settings->key_id, $this->settings->key_secret)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post('https://api.razorpay.com/v1/orders', [
                    'receipt' => $paymentId,
                    'amount' => (int) ($amount * 100),
                    'currency' => app(PaymentSettings::class)->default_currency,
                    'payment_capture' => 1,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("Razorpay Order Creation Failed: " . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error("Razorpay Error: " . $e->getMessage());
            return null;
        }
    }

    public function verifyPayment(array $attributes): bool
    {
        try {
            $api = new Api($this->settings->key_id, $this->settings->key_secret);
            $api->utility->verifyPaymentSignature($attributes);
            return true;
        } catch (SignatureVerificationError $e) {
            Log::error("Razorpay Signature Error: " . $e->getMessage());
            return false;
        }
    }
}
