<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Redirect to dashboard after login based on user role
     */
    public function index()
    {
        $user = Auth::user();

        // Step 1: Get all roles of the user
        // Spatie package returns a collection, e.g., ['instructor']
        $roles = $user->getRoleNames();

        // Agar user ke paas koi role hi nahi hai, to Student dashboard par bhej do (Safe Fallback)
        if ($roles->isEmpty()) {
            return redirect()->route('student.dashboard');
        }

        // User ka main role uthao (First role)
        $userRole = $roles->first();

        // --- FIXED ROUTES (Hardcoded Logic) ---

        // 1. Admin
        if ($userRole === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // 2. Student / Guest / Employee
        // (Agar employee ko student dashboard dikhana hai to yahan rakhein,
        // warna yahan se hata dein to wo niche dynamic wale me chala jayega)
        if (in_array($userRole, ['student', 'guest', 'employee'])) {
            return redirect()->route('student.dashboard');
        }

        // 3. Parent
        if ($userRole === 'parent') {
            return redirect()->route('parent.dashboard');
        }

        // --- DYNAMIC ROUTES (For Everyone Else) ---
        // Yahan wo sab log aayenge jo upar cover nahi huye.
        // Example: Instructor, Manager, Editor, Accountant, Moderator...
        // System automatic URL banayega: /instructor/dashboard, /manager/dashboard

        return redirect()->route('panel.dashboard', ['role' => $userRole]);
    }
}
