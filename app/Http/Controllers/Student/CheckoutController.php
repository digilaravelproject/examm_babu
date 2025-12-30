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
     * Step 1: Show Checkout Page
     */
    public function checkout(Request $request, string $planCode): View
    {
        try {
            $plan = Plan::where('code', $planCode)->where('is_active', true)->firstOrFail();
            $orderSummary = $this->checkoutRepo->orderSummary($plan);

            // Retrieve saved billing info or empty array
            $billingInfo = $request->user()->preferences['billing_information'] ?? [];

            return view('store.checkout.index', [
                'plan' => $plan,
                'order' => $orderSummary,
                'user' => $request->user(),
                'billing_information' => $billingInfo,
                'countries' => $this->getCountriesList(), // Passing full country list
            ]);

        } catch (\Throwable $e) {
            Log::error("Checkout Page Error: " . $e->getMessage());
            abort(404, 'Plan not found or inactive.');
        }
    }

    /**
     * Step 2: Process Form & Initialize Razorpay
     */
    public function processCheckout(Request $request, string $planCode): View|RedirectResponse
    {
        // 1. Validate User Input
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
            $orderSummary = $this->checkoutRepo->orderSummary($plan);

            // 2. Update User Billing Preferences
            $user = $request->user();
            $preferences = $user->preferences ?? [];
            $preferences['billing_information'] = [
                'full_name' => $user->name,
                'email' => $user->email,
                ...$validated // Spread validated address fields
            ];
            $user->preferences = $preferences;
            $user->phone = $validated['phone']; // Update phone if main field exists
            $user->save();

            // 3. Create Order on Razorpay Server
            $paymentRefId = 'pay_' . Str::random(16);
            $razorpayOrder = $this->razorpayRepo->createOrder($paymentRefId, (float) $orderSummary['total']);

            if (!$razorpayOrder) {
                return redirect()->back()->with('error', 'Unable to initiate payment gateway.');
            }

            // 4. Create Pending Payment Entry in DB
            $this->paymentRepo->createPaymentAndSubscription([
                'payment_id' => $paymentRefId,
                'currency' => $this->paymentSettings->default_currency,
                'plan_id' => $plan->id,
                'user_id' => $user->id,
                'total_amount' => $orderSummary['total'],
                'status' => 'pending',
                'duration' => 0,
                'meta_data' => [
                    'razorpay_order_id' => $razorpayOrder['id'],
                    'order_summary' => $orderSummary,
                    'billing_info' => $validated
                ]
            ]);

            // 5. Return View with Razorpay JS
            return view('store.checkout.razorpay', [
                'razorpay_key' => $this->razorpaySettings->key_id,
                'razorpay_order_id' => $razorpayOrder['id'],
                'amount' => $razorpayOrder['amount'],
                'currency' => $razorpayOrder['currency'],
                'name' => 'Exam Babu',
                'description' => "Subscription for " . $plan->name,
                'image' => asset('assets/images/logo.png'),
                'user' => $user,
                'callback_url' => route('razorpay_callback'),
            ]);

        } catch (\Throwable $e) {
            Log::error("Process Checkout Error: " . $e->getMessage());
            return redirect()->route('payment_failed');
        }
    }

    /**
     * Step 3: Handle Callback
     */
    public function handleRazorpayPayment(Request $request): RedirectResponse
    {
        $request->validate([
            'razorpay_payment_id' => 'required',
            'razorpay_order_id' => 'required',
            'razorpay_signature' => 'required',
        ]);

        try {
            $attributes = [
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_order_id'   => $request->razorpay_order_id,
                'razorpay_signature'  => $request->razorpay_signature
            ];

            if (!$this->razorpayRepo->verifyPayment($attributes)) {
                return redirect()->route('payment_failed')->with('error', 'Signature verification failed.');
            }

            // In a real scenario, you would fetch the pending payment via Order ID
            // and update its status to success using PaymentRepository

            // For now, redirect to success
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

    /**
     * Private Helper: Full Country List
     */
    private function getCountriesList(): array
    {
        return [
            'Afghanistan', 'Albania', 'Algeria', 'American Samoa', 'Andorra', 'Angola', 'Anguilla', 'Antarctica', 'Antigua and Barbuda', 'Argentina', 'Armenia', 'Aruba', 'Australia', 'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bermuda', 'Bhutan', 'Bolivia', 'Bosnia and Herzegovina', 'Botswana', 'Bouvet Island', 'Brazil', 'British Antarctic Territory', 'British Indian Ocean Territory', 'British Virgin Islands', 'Brunei', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon', 'Canada', 'Canton and Enderbury Islands', 'Cape Verde', 'Cayman Islands', 'Central African Republic', 'Chad', 'Chile', 'China', 'Christmas Island', 'Cocos [Keeling] Islands', 'Colombia', 'Comoros', 'Congo - Brazzaville', 'Congo - Kinshasa', 'Cook Islands', 'Costa Rica', 'Croatia', 'Cuba', 'Cyprus', 'Czech Republic', 'Côte d’Ivoire', 'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic', 'Dronning Maud Land', 'East Germany', 'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Ethiopia', 'Falkland Islands', 'Faroe Islands', 'Fiji', 'Finland', 'France', 'French Guiana', 'French Polynesia', 'French Southern Territories', 'French Southern and Antarctic Territories', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Gibraltar', 'Greece', 'Greenland', 'Grenada', 'Guadeloupe', 'Guam', 'Guatemala', 'Guernsey', 'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti', 'Heard Island and McDonald Islands', 'Honduras', 'Hong Kong SAR China', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland', 'Isle of Man', 'Israel', 'Italy', 'Jamaica', 'Japan', 'Jersey', 'Johnston Island', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'Kuwait', 'Kyrgyzstan', 'Laos', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macau SAR China', 'Macedonia', 'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Martinique', 'Mauritania', 'Mauritius', 'Mayotte', 'Metropolitan France', 'Mexico', 'Micronesia', 'Midway Islands', 'Moldova', 'Monaco', 'Mongolia', 'Montenegro', 'Montserrat', 'Morocco', 'Mozambique', 'Myanmar [Burma]', 'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'Netherlands Antilles', 'Neutral Zone', 'New Caledonia', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'Niue', 'Norfolk Island', 'North Korea', 'North Vietnam', 'Northern Mariana Islands', 'Norway', 'Oman', 'Pacific Islands Trust Territory', 'Pakistan', 'Palau', 'Palestinian Territories', 'Panama', 'Panama Canal Zone', 'Papua New Guinea', 'Paraguay', 'People\'s Democratic Republic of Yemen', 'Peru', 'Philippines', 'Pitcairn Islands', 'Poland', 'Portugal', 'Puerto Rico', 'Qatar', 'Romania', 'Russia', 'Rwanda', 'Réunion', 'Saint Barthélemy', 'Saint Helena', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Martin', 'Saint Pierre and Miquelon', 'Saint Vincent and the Grenadines', 'Samoa', 'San Marino', 'Saudi Arabia', 'Senegal', 'Serbia', 'Serbia and Montenegro', 'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa', 'South Georgia and the South Sandwich Islands', 'South Korea', 'Spain', 'Sri Lanka', 'Sudan', 'Suriname', 'Svalbard and Jan Mayen', 'Swaziland', 'Sweden', 'Switzerland', 'Syria', 'São Tomé and Príncipe', 'Taiwan', 'Tajikistan', 'Tanzania', 'Thailand', 'Timor-Leste', 'Togo', 'Tokelau', 'Tonga', 'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 'Turks and Caicos Islands', 'Tuvalu', 'U.S. Minor Outlying Islands', 'U.S. Miscellaneous Pacific Islands', 'U.S. Virgin Islands', 'Uganda', 'Ukraine', 'Union of Soviet Socialist Republics', 'United Arab Emirates', 'United Kingdom', 'United States', 'Unknown or Invalid Region', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Vatican City', 'Venezuela', 'Vietnam', 'Wake Island', 'Wallis and Futuna', 'Western Sahara', 'Yemen', 'Zambia', 'Zimbabwe'
        ];
    }
}
