<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Repositories\CheckoutRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\RazorpayRepository;
use App\Settings\PaymentSettings;
use App\Settings\RazorpaySettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutRepository $checkoutRepo,
        protected PaymentRepository $paymentRepo,
        protected RazorpayRepository $razorpayRepo,
        protected PaymentSettings $paymentSettings,
        protected RazorpaySettings $razorpaySettings
    ) {}

    /**
     * Step 1: Show Order Summary Page
     */
    public function checkout(Request $request, string $planCode): View
    {
        try {
            $plan = Plan::where('code', $planCode)->where('is_active', true)->firstOrFail();
            $orderSummary = $this->checkoutRepo->orderSummary($plan);

            return view('store.checkout.index', [
                'plan' => $plan,
                'order' => $orderSummary,
                'user' => $request->user(),
            ]);

        } catch (\Throwable $e) {
            Log::error("Checkout Page Error: " . $e->getMessage());
            abort(404, 'Plan not found or inactive.');
        }
    }

    /**
     * Step 2: Create Razorpay Order & Show Payment Page
     */
    public function processCheckout(Request $request, string $planCode): View|RedirectResponse
    {
        try {
            $plan = Plan::where('code', $planCode)->firstOrFail();
            $orderSummary = $this->checkoutRepo->orderSummary($plan);

            // 1. Create Order on Razorpay Server
            $paymentRefId = 'pay_' . Str::random(16);
            $razorpayOrder = $this->razorpayRepo->createOrder($paymentRefId, (float) $orderSummary['total']);

            if (!$razorpayOrder) {
                return redirect()->back()->with('error', 'Unable to initiate payment with Razorpay.');
            }

            // 2. Create Pending Payment Entry in DB
            $this->paymentRepo->createPaymentAndSubscription([
                'payment_id' => $paymentRefId, // Internal Ref
                'currency' => $this->paymentSettings->default_currency,
                'plan_id' => $plan->id,
                'user_id' => $request->user()->id,
                'total_amount' => $orderSummary['total'],
                'status' => 'pending',
                'duration' => 0, // No sub yet
                'meta_data' => [
                    'razorpay_order_id' => $razorpayOrder['id'],
                    'order_summary' => $orderSummary
                ]
            ]);

            // 3. Return View with Razorpay JS Logic
            return view('store.checkout.razorpay', [
                'razorpay_key' => $this->razorpaySettings->key_id,
                'razorpay_order_id' => $razorpayOrder['id'],
                'amount' => $razorpayOrder['amount'],
                'currency' => $razorpayOrder['currency'],
                'name' => 'Exam Babu',
                'description' => "Payment for " . $plan->name,
                'image' => asset('assets/images/logo.png'), // Ensure logo exists
                'user' => $request->user(),
                'callback_url' => route('razorpay_callback'),
            ]);

        } catch (\Throwable $e) {
            Log::error("Process Checkout Error: " . $e->getMessage());
            return redirect()->route('payment_failed');
        }
    }

    /**
     * Step 3: Handle Callback from Razorpay (Verify & Activate)
     */
    public function handleRazorpayPayment(Request $request): RedirectResponse
    {
        $request->validate([
            'razorpay_payment_id' => 'required',
            'razorpay_order_id' => 'required',
            'razorpay_signature' => 'required',
        ]);

        try {
            // 1. Verify Signature
            $attributes = [
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_order_id'   => $request->razorpay_order_id,
                'razorpay_signature'  => $request->razorpay_signature
            ];

            if (!$this->razorpayRepo->verifyPayment($attributes)) {
                return redirect()->route('payment_failed')->with('error', 'Signature verification failed.');
            }
            return redirect()->route('payment_success');

        } catch (\Throwable $e) {
            Log::error("Razorpay Handler Error: " . $e->getMessage());
            return redirect()->route('payment_failed');
        }
    }

    public function paymentSuccess(): View
    {
        return view('store.checkout.payment_success');
    }

    public function paymentFailed(): View
    {
        return view('store.checkout.payment_failed');
    }
}
