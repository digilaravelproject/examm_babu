<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Settings\ReferralSettings;
use Illuminate\Http\Request;

class ReferralSettingController extends Controller
{
    /**
     * Show the Referral Settings Page
     */
    public function index(ReferralSettings $settings)
    {
        return view('admin.settings.referral', compact('settings'));
    }

    /**
     * Update the Settings
     */
    public function update(Request $request, ReferralSettings $settings)
    {
        $validated = $request->validate([
            'commission_percentage'           => 'required|numeric|min:0|max:100',
            'recurring_commission_percentage' => 'required|numeric|min:0|max:100', // New Setting
            'min_withdrawal_amount'           => 'required|numeric|min:1',
            'cookie_lifetime_days'            => 'required|integer|min:1|max:365',
            'spam_protection_days'            => 'required|integer|min:0|max:365', // Security Setting
            'enable_referral'                 => 'nullable',
        ]);

        // Save Standard Settings
        $settings->commission_percentage = (float) $validated['commission_percentage'];
        $settings->min_withdrawal_amount = (float) $validated['min_withdrawal_amount'];
        $settings->cookie_lifetime_days  = (int) $validated['cookie_lifetime_days'];
        $settings->enable_referral       = $request->has('enable_referral');

        // Save New Logic Settings
        $settings->recurring_commission_percentage = (float) $validated['recurring_commission_percentage'];
        $settings->spam_protection_days            = (int) $validated['spam_protection_days'];

        $settings->save();

        return redirect()->back()->with('success', 'Referral settings updated successfully.');
    }
}
