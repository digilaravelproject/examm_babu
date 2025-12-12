<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Redirect to dashboard after login based on user role
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        if (Auth::user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif (Auth::user()->hasRole(['guest', 'student', 'employee'])) {
            return redirect()->route('student.dashboard');
        } elseif (Auth::user()->hasRole('instructor')) {
            return redirect()->route('instructor.dashboard');
        } elseif (Auth::user()->hasRole('parent')) {
            return redirect()->route('parent.dashboard');
        } else {
            return redirect()->route('student.dashboard');
        }
    }
}
