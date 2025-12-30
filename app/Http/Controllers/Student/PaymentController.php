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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * List user payments
     */
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
     * Download Invoice
     */
    public function downloadInvoice(
        string $paymentId,
        LocalizationSettings $localizationSettings,
        SiteSettings $siteSettings,
        BillingSettings $billingSettings
    )
    {
        try {
            // Check if invoicing is enabled
            if (!$billingSettings->enable_invoicing) {
                return redirect()->back()->with('error', 'Invoicing is currently disabled.');
            }

            // Find payment (AND ensure it belongs to the logged-in user for security)
            $payment = Payment::where('payment_id', $paymentId)
                ->where('user_id', Auth::id())
                ->with('plan')
                ->firstOrFail();

            // Prepare data for the PDF View
            $now = Carbon::now()->timezone($localizationSettings->default_timezone);
            $user = Auth::user();
            $userName = $user->name ?? $user->first_name . ' ' . $user->last_name;

            // Note: Ensure you have a 'pdf.invoice' blade file.
            // If using dompdf, you might want to use PDF::loadView(...) here.
            // For now, returning the view as requested in your snippet.
            return view('pdf.invoice', [
                'payment' => $payment,
                'data'    => $payment->data, // JSON column usually cast to array in model
                'logo'    => asset('storage/'.$siteSettings->logo_path),
                'footer'  => "* Invoice Generated from {$siteSettings->app_name} by {$userName} on {$now->toDayDateTimeString()}",
                'rtl'     => $localizationSettings->default_direction == 'rtl'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Invoice not found or access denied.');
        } catch (\Throwable $e) {
            Log::error("Invoice Download Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to generate invoice.');
        }
    }
}
