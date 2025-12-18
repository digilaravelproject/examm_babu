<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trait SubscriptionTrait
 * Handles user subscription logic with optimized caching for Laravel 12.
 */
trait SubscriptionTrait
{
    /**
     * Check if the user has an active subscription to a specific category and optionally a feature.
     * Includes Cache Layer for performance.
     *
     * @param int|string $categoryId
     * @param string|null $featureCode
     * @return bool
     */
    public function hasActiveSubscription(int|string $categoryId, ?string $featureCode = null): bool
    {
        // Unique Cache Key per user, category and feature
        // Format: user_1_sub_cat_5_feat_quiz_module
        $cacheKey = "user_{$this->id}_sub_cat_{$categoryId}_feat_" . ($featureCode ?? 'any');

        // Cache results for 30 minutes to reduce DB load
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($categoryId, $featureCode) {

            $subscription = $this->subscriptions()
                ->where('category_id', $categoryId)
                ->where('status', 'active')
                ->where('ends_at', '>', now())
                ->with(['plan.features' => function ($query) {
                    $query->select('features.id', 'features.code'); // Only fetch necessary columns
                }])
                ->first();

            // No active subscription found
            if (! $subscription) {
                return false;
            }

            // If only category access check is required
            if (is_null($featureCode)) {
                return true;
            }

            // Use Laravel Collection 'contains' for better readability and performance
            return $subscription->plan->features->contains('code', $featureCode);
        });
    }

    /**
     * Check if the user has a pending bank payment for a specific plan.
     * This is usually not cached to ensure real-time status check.
     *
     * @param int|string $planId
     * @return bool
     */
    public function hasPendingBankPayment(int|string $planId): bool
    {
        return $this->payments()
            ->where('plan_id', $planId)
            ->where('payment_processor', 'bank')
            ->where('status', 'pending')
            ->exists(); // 'exists()' is much faster than 'count() > 0'
    }

    /**
     * Get the current active subscription instance for a specific category.
     *
     * @param int|string $categoryId
     * @return mixed
     */
    public function getActiveSubscription(int|string $categoryId)
    {
        return $this->subscriptions()
            ->where('category_id', $categoryId)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->first();
    }

    /**
     * Clear all subscription related cache for this user.
     * Call this method whenever a user's subscription is updated or created.
     *
     * @param int|string|null $categoryId
     * @return void
     */
    public function flushSubscriptionCache(int|string $categoryId = null): void
    {
        // If your app uses Redis, it's better to use Cache Tags.
        // For file/database cache, we have to forget specific keys.
        // Note: This is a basic implementation.
        if ($categoryId) {
            Cache::forget("user_{$this->id}_sub_cat_{$categoryId}_feat_any");
        }

        // If you have many features, consider using a more dynamic key clearing strategy
        // or Laravel's Cache Tags: Cache::tags(['user_'.$this->id])->flush();
    }
}
