<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Plan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PaymentController extends Controller
{
    // 1. List All Payments
    public function index(Request $request)
    {
        $query = Payment::with(['user', 'plan'])->latest();

        // Filters (Search)
        if ($request->filled('payment_id')) {
            $query->where('payment_id', 'like', '%' . $request->payment_id . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->paginate(10);

        // AJAX Request for Filter/Search
        if ($request->ajax()) {
            return view('admin.payments.partials.table', compact('payments'))->render();
        }

        return view('admin.payments.index', compact('payments'));
    }

    // 2. Approve/Reject Manual (Bank) Payment
    public function authorizePayment(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $payment = Payment::with('plan')->findOrFail($id);

        if ($payment->status == 'success') {
            return response()->json(['success' => false, 'message' => 'Payment is already approved!']);
        }

        // Agar Admin Reject kare
        if ($request->status == 'rejected') {
            $payment->update(['status' => 'failed']);
            return response()->json(['success' => true, 'message' => 'Payment rejected successfully.']);
        }

        // Agar Admin Approve kare -> Create Subscription
        if ($request->status == 'approved') {

            // 1. Payment Success karein
            $payment->update(['status' => 'success']);

            // 2. Subscription Create karein
            $plan = $payment->plan;

            $subscription = Subscription::create([
                'user_id' => $payment->user_id,
                'plan_id' => $plan->id,
                'payment_id' => $payment->payment_id,
                'status' => 'active',
                'starts_at' => Carbon::now(),
                'ends_at' => Carbon::now()->addMonths($plan->duration), // Duration months mein hai to
                'code' => 'SUB-' . strtoupper(uniqid()),
            ]);

            return response()->json(['success' => true, 'message' => 'Payment approved & Subscription activated!']);
        }
    }

    public function show(Request $request, $id)
    {
        $payment = Payment::with(['user', 'plan'])->findOrFail($id);

        if ($request->ajax()) {
            return view('admin.payments.partials.details-drawer', compact('payment'))->render();
        }

        return abort(404);
    }

    // 4. Update Status manually via Drawer
    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        $payment->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Payment status updated!');
    }

    public function downloadInvoice($id)
    {
        $payment = Payment::with(['user', 'plan'])->findOrFail($id);

        $data = [
            'payment' => $payment,
            'company_name' => 'Exam Babu', // Apni Application ka naam
            'company_address' => '123, Education Hub, India', // Apna Address
            'date' => $payment->created_at->format('d M, Y')
        ];

        $pdf = Pdf::loadView('admin.invoices.payment_invoice', $data);

        return $pdf->download('invoice_' . $payment->payment_id . '.pdf');
    }
}
