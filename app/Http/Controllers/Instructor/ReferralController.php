<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Plan;
use App\Settings\ReferralSettings;
use Illuminate\View\View;

class ReferralController extends Controller
{
    /**
     * Display the referral dashboard.
     *
     * @param ReferralSettings $settings
     * @return \Illuminate\View\View
     */
    public function index(ReferralSettings $settings): View
    {
        $user = Auth::user();
        $user->load('referralSetting');


        $canWithdraw = $user->wallet_balance >= $settings->min_withdrawal_amount;

        $myCommissionRate = $user->referralSetting->commission_percentage
                            ?? $settings->commission_percentage;

        $myRecurringRate = $user->referralSetting->recurring_commission_percentage
                           ?? $settings->recurring_commission_percentage;

        return view('instructor.referral.index', [
            'user'           => $user,
            'plans'          => Plan::where('is_active', true)->get(),
            'referralLink'   => route('home', ['ref' => $user->referral_code]),
            'totalReferrals' => $user->referrals()->count(),
            'totalEarnings'  => $user->wallet_balance,

            'minLimit'       => $settings->min_withdrawal_amount,
            'canWithdraw'    => $canWithdraw,

            'commissionRate' => $myCommissionRate,
            'recurringRate'  => $myRecurringRate,
        ]);
    }
}
