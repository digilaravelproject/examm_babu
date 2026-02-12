<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Referral;
use App\Models\WithdrawalRequest;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    /**
     * Page 1: Referral History (Audit Log)
     * Shows who referred whom and commission details.
     */
    public function history()
    {
        // Eager load relationships for performance
        $referrals = Referral::with(['referrer', 'referee', 'payment.plan'])
            ->latest()
            ->paginate(15);

        return view('admin.referral.history', compact('referrals'));
    }

    /**
     * Page 2: Withdrawal Requests
     * Shows pending payout requests.
     */
    public function withdrawals(Request $request)
    {
        $query = WithdrawalRequest::with('user')->latest();

        // Optional: Filter by status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->paginate(15);

        return view('admin.referral.withdrawals', compact('withdrawals'));
    }

    /**
     * Action: Approve Payout
     * Admin manually pays and enters Transaction ID.
     */
    public function approveWithdrawal(Request $request, $id)
    {
        $request->validate([
            'transaction_id' => 'required|string|max:100', // Bank Ref / UPI Ref ID
            'admin_note'     => 'nullable|string|max:255',
        ]);

        $withdrawal = WithdrawalRequest::findOrFail($id);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Request is already processed.');
        }

        $withdrawal->update([
            'status' => 'approved',
            'transaction_id' => $request->transaction_id,
            'admin_note' => $request->admin_note,
        ]);

        // Note: Wallet se paisa pehle hi kat chuka hai (Request time par),
        // isliye ab wallet me koi change nahi karna hai. Bas status update.

        return back()->with('success', 'Withdrawal marked as Paid.');
    }

    /**
     * Action: Reject Payout (With Refund)
     * If details are wrong, reject and refund money to wallet.
     */
    public function rejectWithdrawal(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'required|string|max:255', // Reason dena zaroori hai
        ]);

        $withdrawal = WithdrawalRequest::findOrFail($id);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Request is already processed.');
        }

        DB::transaction(function () use ($withdrawal, $request) {
            // 1. Mark as Rejected
            $withdrawal->update([
                'status' => 'rejected',
                'admin_note' => $request->admin_note,
            ]);

            // 2. REFUND Logic: Create Credit Transaction
            WalletTransaction::create([
                'user_id' => $withdrawal->user_id,
                'amount'  => $withdrawal->amount, // Positive Amount (Credit)
                'type'    => 'credit',
                'source'  => 'refund',
                'description' => "Refund for Rejected Withdrawal #{$withdrawal->id}. Reason: {$request->admin_note}",
                'reference_id' => $withdrawal->id,

                // Calculate logic: Current Balance + Refund Amount
                'running_balance' => $withdrawal->user->wallet_balance + $withdrawal->amount
            ]);
        });

        return back()->with('success', 'Request rejected and amount refunded to wallet.');
    }
}
