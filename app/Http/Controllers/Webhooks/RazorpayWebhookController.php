<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Repositories\PaymentRepository;
use App\Repositories\RazorpayRepository;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    public function __construct(
        protected RazorpayRepository $razorpayRepository,
        protected PaymentRepository $paymentRepository
    ) {}

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');

        if (!$signature) {
             Log::warning("Razorpay Webhook: Missing Signature Header");
             return response()->json(['status' => 'missing signature'], 400);
        }

        if (!$this->razorpayRepository->verifyWebhookSignature($payload, $signature)) {
            Log::warning("Razorpay Webhook: Invalid Signature");
            return response()->json(['status' => 'invalid signature'], 400);
        }

        $data = json_decode($payload, true);
        $event = $data['event'] ?? null;

        Log::info("Razorpay Webhook Received: " . $event);

        if ($event === 'payment.captured') {
            return $this->handlePaymentCaptured($data);
        }

        return response()->json(['status' => 'ignored']);
    }

    protected function handlePaymentCaptured(array $data)
    {
        $razorpayPaymentId = $data['payload']['payment']['entity']['id'];
        $razorpayOrderId = $data['payload']['payment']['entity']['order_id'];

        // Find payment by razorpay_order_id in schemaless attributes
        // Spatie Schemaless attributes can be queried using data->property syntax in Laravel
        $payment = Payment::where('data->razorpay_order_id', $razorpayOrderId)
            ->where('status', 'pending')
            ->first();

        if (!$payment) {
            Log::info("Razorpay Webhook: Payment not found or already processed for Order ID: {$razorpayOrderId}");
            return response()->json(['status' => 'not found or processed']);
        }

        // Confirm Payment & Activate
        $this->paymentRepository->confirmPayment($payment, $razorpayPaymentId);

        // Handle Referral
        ReferralService::handleReferral($payment->user, $payment);

        Log::info("Razorpay Webhook: Payment Activated for Order ID: {$razorpayOrderId}");

        return response()->json(['status' => 'success']);
    }
}
