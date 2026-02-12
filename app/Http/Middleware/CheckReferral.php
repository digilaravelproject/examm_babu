<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use App\Settings\ReferralSettings; // Import Settings
use Symfony\Component\HttpFoundation\Response;

class CheckReferral
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Settings load karein
        $settings = app(ReferralSettings::class);

        // 2. Check karein agar System Disabled hai to cookie set na karein
        if (! $settings->enable_referral) {
            return $next($request);
        }

        $referralCode = $request->query('ref');

        if ($referralCode) {
            // Prevent self-referral
            if (Auth::check() && Auth::user()->referral_code === $referralCode) {
                return $next($request);
            }

            // 3. Admin ke set kiye hue days lein (Default 30 days agar null ho)
            $days = $settings->cookie_lifetime_days ?: 30;

            // 4. Laravel Cookie minutes me leta hai, so convert Days -> Minutes
            // Days * 24 Hours * 60 Minutes
            $minutes = $days * 24 * 60;

            // Queue the cookie dynamically
            Cookie::queue('referral_code', $referralCode, $minutes);
        }

        return $next($request);
    }
}
