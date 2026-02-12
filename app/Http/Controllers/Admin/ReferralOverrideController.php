<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserReferralSetting;
use App\Settings\ReferralSettings;
use Illuminate\Http\Request;

class ReferralOverrideController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $users = User::query()
            ->with('referralSetting')
            ->when($search, function ($q) use ($search) {
                $q->where(function($subQuery) use ($search) {
                    $subQuery->where('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%")
                             ->orWhere('referral_code', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15);

        // --- AJAX LOGIC START ---
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.referral.partials.table_rows', compact('users'))->render(),
                'pagination' => (string) $users->links()
            ]);
        }
        // --- AJAX LOGIC END ---

        $globalSettings = app(ReferralSettings::class);

        return view('admin.referral.users_list', compact('users', 'globalSettings'));
    }

    // Update method remains exactly the same as before
    public function update(Request $request, User $user)
    {
        $request->validate([
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'recurring_commission_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($request->commission_percentage === null && $request->recurring_commission_percentage === null) {
            $user->referralSetting()->delete();
            return back()->with('success', 'User reset to Global Default settings.');
        }

        UserReferralSetting::updateOrCreate(
            ['user_id' => $user->id],
            [
                'commission_percentage' => $request->commission_percentage,
                'recurring_commission_percentage' => $request->recurring_commission_percentage,
            ]
        );

        return back()->with('success', "Referral rates updated for {$user->full_name}.");
    }
}
