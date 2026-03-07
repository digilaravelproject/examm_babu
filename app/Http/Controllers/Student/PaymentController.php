<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Settings\BillingSettings;
use App\Settings\LocalizationSettings;
use App\Settings\SiteSettings;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf; // Import DomPDF

class PaymentController extends Controller
{
   public function index(BillingSettings $billingSettings): View
    {
        $payments = Payment::with('plan')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('student.payments.index', [
            'payments' => $payments,
            'enable_invoice' => $billingSettings->enable_invoicing
        ]);
    }

    /**
     * 1. PREVIEW INVOICE (Web Page)
     */
    public function previewInvoice(string $paymentId, SiteSettings $siteSettings, LocalizationSettings $localizationSettings, BillingSettings $billingSettings)
    {
        $payment = $this->getPaymentSecurely($paymentId);

        return view('student.payments.invoice-preview', [
            'payment' => $payment,
            'data'    => $payment->data,
            'logo'    => asset('storage/'.$siteSettings->logo_path),
            'siteSettings' => $siteSettings,
            'billingSettings' => $billingSettings,
            'currencySymbol' => $payment->currency // Or logic to get symbol
        ]);
    }

    /**
     * 2. DOWNLOAD PDF (Actual File)
     */
    public function downloadInvoice(string $paymentId, SiteSettings $siteSettings, LocalizationSettings $localizationSettings, BillingSettings $billingSettings)
    {
        try {
            $payment = $this->getPaymentSecurely($paymentId);

            $now = Carbon::now()->timezone($localizationSettings->default_timezone);
            $user = Auth::user();
            $userName = $user->name ?? $user->first_name . ' ' . $user->last_name;

            $data = [
                'payment' => $payment,
                'data'    => $payment->data,
                'logo'    => public_path('storage/'.$siteSettings->logo_path), // DomPDF needs absolute path or base64
                'footer'  => "* Invoice Generated from {$siteSettings->app_name} by {$userName} on {$now->toDayDateTimeString()}",
                'rtl'     => $localizationSettings->default_direction == 'rtl',
                'billingSettings' => $billingSettings
            ];

            // Load the View specifically designed for PDF (no headers/buttons)
            $pdf = Pdf::loadView('pdf.invoice', $data);

            // Set paper size
            $pdf->setPaper('a4', 'portrait');

            return $pdf->download('Invoice-'.$payment->invoice_id.'.pdf');

        } catch (\Exception $e) {
            Log::error("Invoice PDF Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to generate PDF. Please try again.');
        }
    }

    /**
     * Helper to fetch payment and check ownership
     */
    private function getPaymentSecurely($paymentId)
    {
        return Payment::where('payment_id', $paymentId) // Or just 'id' depending on your route
            ->where('user_id', Auth::id())
            ->with('plan')
            ->firstOrFail();
    }
}
