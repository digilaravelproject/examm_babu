<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

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
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validation rules update kiye hain
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'], // DB me null allowed tha
            'user_name' => ['required', 'string', 'max:255', 'unique:'.User::class, 'alpha_dash'], // alpha_dash means no spaces
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2. User Create with new columns
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'user_name' => $request->user_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true, // Default active
        ]);

        // 3. Assign 'student' role immediately
        // Make sure User model me "use HasRoles" trait add ho
        $user->assignRole('student');

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Real-time username check logic
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
