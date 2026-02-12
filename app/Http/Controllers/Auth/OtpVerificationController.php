<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OtpVerificationController extends Controller
{
    // OTP Form Show karega
    public function show()
    {
        return view('auth.verify-otp');
    }

    // OTP Verify karega
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6'
        ]);

        $user = Auth::user();

        // Check if OTP matches
        if ($user->verification_code !== $request->otp) {
            return back()->withErrors(['otp' => 'Invalid OTP entered.']);
        }

        // Check Expiry
        if (Carbon::now()->greaterThan($user->verification_code_expires_at)) {
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
        }

        // Verify User
        $user->forceFill([
            'email_verified_at' => now(),
            'verification_code' => null, // Clear code
            'verification_code_expires_at' => null,
        ])->save();

        return redirect()->route('dashboard')->with('status', 'Email Verified Successfully!');
    }

    // Resend OTP Feature
    public function resend()
    {
        $user = Auth::user();
        $otp = rand(100000, 999999);

        $user->forceFill([
            'verification_code' => $otp,
            'verification_code_expires_at' => Carbon::now()->addMinutes(10),
        ])->save();

        \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\SendOtpMail($otp));

        return back()->with('status', 'New OTP sent to your email.');
    }
}
