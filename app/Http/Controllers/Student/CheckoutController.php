<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Plan;
use App\Repositories\CheckoutRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\RazorpayRepository;
use App\Settings\PaymentSettings;
use App\Settings\RazorpaySettings;
use Carbon\Carbon;
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
    public function checkout(Request $request, string $planCode): View|RedirectResponse
    {
        try {
            $plan = Plan::where('code', $planCode)->where('is_active', true)->firstOrFail();
            $orderSummary = $this->checkoutRepo->orderSummary($plan);

            // 1. Check for Free Plan (Skip Checkout Page)
            if ($orderSummary['total'] <= 0) {
                return $this->processFreePlan($request->user(), $plan, $orderSummary);
            }

            $billingInfo = $request->user()->preferences['billing_information'] ?? [];

            return view('store.checkout.index', [
                'plan' => $plan,
                'order' => $orderSummary,
                'user' => $request->user(),
                'billing_information' => $billingInfo,
                'countries' => $this->getCountriesList(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Checkout Page Error: " . $e->getMessage());
            return redirect()->route('welcome')->with('error', 'Plan not found or inactive.');
        }
    }

    /**
     * Step 2: Process Form & Initialize Razorpay
     */
    public function processCheckout(Request $request, string $planCode): View|RedirectResponse
    {
        // 1. Validation
        $validated = $request->validate([
            'address' => 'required|string|max:255',
            'city'    => 'required|string|max:100',
            'state'   => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'zip'     => 'required|string|max:20',
            'phone'   => 'required|string|max:20',
        ]);

        try {
            $plan = Plan::where('code', $planCode)->firstOrFail();
            $user = $request->user();

            // 2. Check Existing Subscription (Security Check)
            // Agar user ke paas pehle se active plan hai same category ka, to roko.
            $hasActiveSub = $user->subscriptions()
                ->where('category_id', $plan->category_id)
                ->where('status', 'active')
                ->where('ends_at', '>', now())
                ->exists();

            if ($hasActiveSub) {
                return redirect()->back()->withErrors(['error' => 'You already have an active subscription for this category.']);
            }

            $orderSummary = $this->checkoutRepo->orderSummary($plan);

            // 3. Handle Free Plan via POST (Double Check)
            if ($orderSummary['total'] <= 0) {
                return $this->processFreePlan($user, $plan, $orderSummary);
            }

            // 4. Update User Preferences
            $preferences = $user->preferences ?? [];
            $preferences['billing_information'] = [
                'full_name' => $user->name,
                'email' => $user->email,
                ...$validated
            ];
            $user->preferences = $preferences;
            // $user->phone = $validated['phone'];
            $user->save();

            // 5. Create Order on Razorpay
            $paymentRefId = 'pay_' . Str::random(16);
            $razorpayOrder = $this->razorpayRepo->createOrder($paymentRefId, (float) $orderSummary['total']);

            if (!$razorpayOrder) {
                return redirect()->back()->with('error', 'Unable to initiate payment gateway.');
            }

            // 6. Create "Pending" Payment Record in DB
            $this->paymentRepo->createPayment([
                'payment_id' => $paymentRefId,
                'currency' => $this->paymentSettings->default_currency,
                'plan_id' => $plan->id,
                'user_id' => $user->id,
                'total_amount' => $orderSummary['total'],
                'status' => 'pending',
                'payment_processor' => 'razorpay',
                'meta_data' => [
                    'razorpay_order_id' => $razorpayOrder['id'],
                    'order_summary' => $orderSummary,
                    'billing_info' => $validated
                ]
            ]);

            // 7. Open Razorpay View
            return view('store.checkout.razorpay', [
                'razorpay_key' => $this->razorpaySettings->key_id,
                'razorpay_order_id' => $razorpayOrder['id'],
                'amount' => $razorpayOrder['amount'], // in paise
                'currency' => $razorpayOrder['currency'],
                'name' => 'Exam Babu',
                'description' => "Subscription for " . $plan->name,
                'image' => asset('assets/images/favicon.jpg'),
                'user' => $user,
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

            // 2. Find Pending Payment by Razorpay Order ID (Stored in meta_data)
            $payment = Payment::where('data->razorpay_order_id', $request->razorpay_order_id)
                ->where('status', 'pending')
                ->first();

            if (!$payment) {
                // If not found by JSON, try finding by payment_id logic if implemented differently,
                // else fail safely to avoid ghost subscriptions.
                Log::error("Payment record not found for Order ID: " . $request->razorpay_order_id);
                return redirect()->route('payment_failed')->with('error', 'Payment record not found.');
            }

            // 3. Confirm Payment & Create Subscription
            $this->paymentRepo->confirmPayment($payment, $request->razorpay_payment_id);

            return redirect()->route('payment_success');
        } catch (\Throwable $e) {
            Log::error("Razorpay Handler Error: " . $e->getMessage());
            return redirect()->route('payment_failed');
        }
    }

    /**
     * Helper: Process Free Plan
     */
    private function processFreePlan($user, $plan, $orderSummary): RedirectResponse
    {
        try {
            $paymentRefId = 'free_' . Str::random(16);

            // Create Success Payment & Subscription Immediately
            $this->paymentRepo->createPaymentAndSubscription([
                'payment_id' => $paymentRefId,
                'currency' => $this->paymentSettings->default_currency,
                'plan_id' => $plan->id,
                'user_id' => $user->id,
                'total_amount' => 0,
                'status' => 'success',
                'payment_processor' => 'free',
                'duration' => $plan->duration ?? 12,
                'transaction_id' => 'FREE-' . strtoupper(Str::random(8)),
                'meta_data' => [
                    'order_summary' => $orderSummary
                ]
            ]);

            return redirect()->route('payment_success');
        } catch (\Exception $e) {
            Log::error("Free Plan Error: " . $e->getMessage());
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

    // Helper: Country List
    private function getCountriesList(): array
    {
        return [
            'India',
            'Afghanistan',
            'Albania',
            'Algeria',
            'Andorra',
            'Angola',
            'Argentina',
            'Armenia',
            'Australia',
            'Austria',
            'Azerbaijan',
            'Bahamas',
            'Bahrain',
            'Bangladesh',
            'Barbados',
            'Belarus',
            'Belgium',
            'Belize',
            'Benin',
            'Bhutan',
            'Bolivia',
            'Bosnia and Herzegovina',
            'Botswana',
            'Brazil',
            'Brunei',
            'Bulgaria',
            'Burkina Faso',
            'Burundi',
            'Cambodia',
            'Cameroon',
            'Canada',
            'Cape Verde',
            'Central African Republic',
            'Chad',
            'Chile',
            'China',
            'Colombia',
            'Comoros',
            'Congo',
            'Costa Rica',
            'Croatia',
            'Cuba',
            'Cyprus',
            'Czech Republic',
            'Denmark',
            'Djibouti',
            'Dominica',
            'Dominican Republic',
            'East Timor',
            'Ecuador',
            'Egypt',
            'El Salvador',
            'Equatorial Guinea',
            'Eritrea',
            'Estonia',
            'Ethiopia',
            'Fiji',
            'Finland',
            'France',
            'Gabon',
            'Gambia',
            'Georgia',
            'Germany',
            'Ghana',
            'Greece',
            'Grenada',
            'Guatemala',
            'Guinea',
            'Guinea-Bissau',
            'Guyana',
            'Haiti',
            'Honduras',
            'Hungary',
            'Iceland',
            'Indonesia',
            'Iran',
            'Iraq',
            'Ireland',
            'Israel',
            'Italy',
            'Jamaica',
            'Japan',
            'Jordan',
            'Kazakhstan',
            'Kenya',
            'Kiribati',
            'Kuwait',
            'Kyrgyzstan',
            'Laos',
            'Latvia',
            'Lebanon',
            'Lesotho',
            'Liberia',
            'Libya',
            'Liechtenstein',
            'Lithuania',
            'Luxembourg',
            'Macedonia',
            'Madagascar',
            'Malawi',
            'Malaysia',
            'Maldives',
            'Mali',
            'Malta',
            'Mauritania',
            'Mauritius',
            'Mexico',
            'Moldova',
            'Monaco',
            'Mongolia',
            'Montenegro',
            'Morocco',
            'Mozambique',
            'Myanmar',
            'Namibia',
            'Nauru',
            'Nepal',
            'Netherlands',
            'New Zealand',
            'Nicaragua',
            'Niger',
            'Nigeria',
            'Norway',
            'Oman',
            'Pakistan',
            'Palau',
            'Panama',
            'Papua New Guinea',
            'Paraguay',
            'Peru',
            'Philippines',
            'Poland',
            'Portugal',
            'Qatar',
            'Romania',
            'Russia',
            'Rwanda',
            'Saint Kitts and Nevis',
            'Saint Lucia',
            'Saint Vincent',
            'Samoa',
            'San Marino',
            'Saudi Arabia',
            'Senegal',
            'Serbia',
            'Seychelles',
            'Sierra Leone',
            'Singapore',
            'Slovakia',
            'Slovenia',
            'Solomon Islands',
            'Somalia',
            'South Africa',
            'Spain',
            'Sri Lanka',
            'Sudan',
            'Suriname',
            'Swaziland',
            'Sweden',
            'Switzerland',
            'Syria',
            'Taiwan',
            'Tajikistan',
            'Tanzania',
            'Thailand',
            'Togo',
            'Tonga',
            'Trinidad and Tobago',
            'Tunisia',
            'Turkey',
            'Turkmenistan',
            'Tuvalu',
            'Uganda',
            'Ukraine',
            'United Arab Emirates',
            'United Kingdom',
            'United States',
            'Uruguay',
            'Uzbekistan',
            'Vanuatu',
            'Vatican City',
            'Venezuela',
            'Vietnam',
            'Yemen',
            'Zambia',
            'Zimbabwe'
        ];
    }
}
