<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Models\WalletTransaction;
use App\Settings\ReferralSettings;
use Illuminate\Validation\ValidationException;

class WithdrawalController extends Controller
{
    /**
     * Store a new withdrawal request.
     */
    public function store(Request $request, ReferralSettings $settings)
    {
        $minAmount = $settings->min_withdrawal_amount;

        // 1. Dynamic Validation Rules
        $rules = [
            'amount'         => "required|numeric|min:{$minAmount}",
            'payment_method' => 'required|in:UPI,Bank Transfer',
        ];

        if ($request->payment_method === 'UPI') {
            $rules['upi_id'] = 'required|string|max:100';
        } else {
            $rules['bank_name']           = 'required|string|max:100';
            $rules['ifsc_code']           = 'required|string|max:20';
            $rules['account_holder_name'] = 'required|string|max:100';
            $rules['account_number']      = 'required|string|confirmed|max:50';
        }

        $request->validate($rules);

        $amount = $request->amount;
        $currentUser = Auth::user();

        // 2. Initial Balance Check (Fail fast)
        if ($currentUser->wallet_balance < $amount) {
            return back()->with('error', "Insufficient wallet balance.");
        }

        // Prepare Payment Details JSON
        $detailsData = $request->payment_method === 'UPI'
            ? ['type' => 'UPI', 'upi_id' => $request->upi_id]
            : [
                'type'           => 'Bank Transfer',
                'bank_name'      => $request->bank_name,
                'ifsc_code'      => $request->ifsc_code,
                'account_holder' => $request->account_holder_name,
                'account_number' => $request->account_number
            ];

        try {
            DB::transaction(function () use ($currentUser, $amount, $request, $detailsData) {

                // 3. Lock the user row to prevent double spending (Race Condition)
                $user = User::where('id', $currentUser->id)->lockForUpdate()->first();

                // Re-check balance after lock
                if ($user->wallet_balance < $amount) {
                    throw ValidationException::withMessages(['amount' => 'Insufficient wallet balance.']);
                }

                // 4. Create Withdrawal Request
                $withdrawal = WithdrawalRequest::create([
                    'user_id'         => $user->id,
                    'amount'          => $amount,
                    'payment_method'  => $request->payment_method,
                    'payment_details' => json_encode($detailsData),
                    'status'          => 'pending'
                ]);

                // 5. Deduct Balance from User Table
                $user->decrement('wallet_balance', $amount);

                // Calculate running balance after deduction
                $newBalance = $user->wallet_balance;

                // 6. Log Wallet Transaction
                WalletTransaction::create([
                    'user_id'         => $user->id,
                    'amount'          => -$amount, // Negative for Debit
                    'type'            => 'debit',
                    'source'          => 'withdrawal',
                    'description'     => 'Withdrawal Request Initiated',
                    'reference_id'    => $withdrawal->id,
                    'running_balance' => $newBalance
                ]);
            });

            return back()->with('success', 'Withdrawal request submitted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Transaction failed: ' . $e->getMessage());
        }
    }
}
