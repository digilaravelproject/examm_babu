<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    /**
     * List user subscriptions
     */
    public function index(): View
    {
        // Fetch subscriptions for the logged-in user
        $subscriptions = Subscription::with(['plan' => function($query) {
                $query->with('features'); // Eager load features for display
            }])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('student.subscriptions.index', [
            'subscriptions' => $subscriptions
        ]);
    }

    /**
     * Cancel a specific subscription
     */
    public function cancelSubscription(string $id): RedirectResponse
    {
        // Security: Find subscription ONLY if it belongs to the authenticated user
        $subscription = Subscription::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        // Check if already cancelled or expired to prevent redundant actions
        if ($subscription->status !== 'active') {
            return redirect()->back()->with('error', 'This subscription is not active.');
        }

        // Update status
        $subscription->status = 'cancelled';
        $subscription->save(); // 'save()' is generally preferred over 'update()' for single model instances in Laravel 12

        return redirect()->back()->with('success', 'Subscription has been successfully cancelled.');
    }
}
