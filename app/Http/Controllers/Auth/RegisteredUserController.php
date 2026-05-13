<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\SendOtpMail; // Ensure ye Mail class aapne banayi ho
use App\Mail\WelcomeUserMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Carbon\Carbon;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validation
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'user_name' => ['required', 'string', 'max:255', 'unique:' . User::class, 'alpha_dash'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2. Generate OTP
        $otp = rand(100000, 999999);

        // 3. Create User
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'user_name' => $request->user_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true,
            'verification_code' => $otp,
            'verification_code_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // 4. Assign Role
        // Ensure Spatie permissions are setup correctly
        $user->assignRole('student');

        // 5. Send OTP Mail Only (Custom)
        try {
            Mail::to($user->email)->queue(new WelcomeUserMail($user, $request->password, 'Student'));
            Mail::to($user->email)->send(new SendOtpMail($otp));
        } catch (\Exception $e) {
            // Mail fail hone par bhi process na ruke
        }

        // --- CHANGE HERE ---
        // Humne ye line comment kar di hai taaki default verification link na jaye.
        // event(new Registered($user));
        // -------------------

        // 6. Login User
        Auth::login($user);

        // 7. Redirect to OTP Verification Page
        return redirect()->route('otp.verify');
    }

    /**
     * Check username availability
     */
    public function checkUsername(Request $request)
    {
        $username = $request->input('username');

        if (!$username) {
            return response()->json(['status' => 'empty']);
        }

        $exists = User::where('user_name', $username)->exists();

        return response()->json([
            'status' => $exists ? 'taken' : 'available'
        ]);
    }
}
