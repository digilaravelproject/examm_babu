<?php

namespace App\Services;

use App\Models\MicroCategory;
use App\Models\UserGroup;
use App\Models\ExamType;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MicroCategoryService
{
    /**
     * Create related entities for a new MicroCategory
     * (UserGroup, ExamType, and Plan)
     */
    public function createRelatedEntities(MicroCategory $microCategory): void
    {
        DB::transaction(function () use ($microCategory) {
            try {
                // 1. Create UserGroup
                $userGroup = UserGroup::create([
                    'name' => $microCategory->name,
                    'is_active' => true,
                    'is_private' => false,
                ]);

                Log::info("UserGroup created: {$userGroup->name} (ID: {$userGroup->id})");

                // 2. Create ExamType
                $examType = ExamType::create([
                    'name' => $microCategory->name,
                    'is_active' => true,
                ]);

                Log::info("ExamType created: {$examType->name} (ID: {$examType->id})");

                // 3. Create Plan
                $sortOrder = Plan::count() + 1;

                $plan = Plan::create([
                    'name' => $microCategory->name,
                    'category_id' => $microCategory->id,
                    'category_type' => MicroCategory::class,
                    'duration' => 12, // 12 months
                    'price' => 0,
                    'currency' => 'INR',
                    'has_discount' => false,
                    'discount_percentage' => 0,
                    'has_trial' => false,
                    'trial_days' => 0,
                    'feature_restrictions' => 0,
                    'sort_order' => $sortOrder,
                    'is_popular' => false,
                    'is_active' => true,
                ]);

                Log::info("Plan created: {$plan->name} (ID: {$plan->id}, Sort: {$sortOrder})");
                Log::info("✅ All related entities created successfully for MicroCategory: {$microCategory->name}");

            } catch (\Exception $e) {
                Log::error("❌ Failed to create related entities for MicroCategory {$microCategory->id}: " . $e->getMessage());
                throw $e; // Re-throw to trigger transaction rollback
            }
        });
    }

    /**
     * Sync names of related entities when MicroCategory name is updated
     */
    public function syncRelatedEntityNames(MicroCategory $microCategory, string $oldName): void
    {
        DB::transaction(function () use ($microCategory, $oldName) {
            try {
                $updated = 0;

                // 1. Update UserGroup name (find by old name)
                $userGroupUpdated = UserGroup::where('name', $oldName)
                    ->update(['name' => $microCategory->name]);
                $updated += $userGroupUpdated;

                if ($userGroupUpdated > 0) {
                    Log::info("UserGroup name updated: {$oldName} → {$microCategory->name}");
                }

                // 2. Update ExamType name
                $examTypeUpdated = ExamType::where('name', $oldName)
                    ->update(['name' => $microCategory->name]);
                $updated += $examTypeUpdated;

                if ($examTypeUpdated > 0) {
                    Log::info("ExamType name updated: {$oldName} → {$microCategory->name}");
                }

                // 3. Update Plan name (linked to this MicroCategory)
                $planUpdated = Plan::where('category_id', $microCategory->id)
                    ->where('category_type', MicroCategory::class)
                    ->where('name', $oldName)
                    ->update(['name' => $microCategory->name]);
                $updated += $planUpdated;

                if ($planUpdated > 0) {
                    Log::info("Plan name updated: {$oldName} → {$microCategory->name}");
                }

                Log::info("✅ Synced {$updated} related entities for MicroCategory: {$oldName} → {$microCategory->name}");

            } catch (\Exception $e) {
                Log::error("❌ Failed to sync related entities for MicroCategory {$microCategory->id}: " . $e->getMessage());
                throw $e; // Re-throw to trigger transaction rollback
            }
        });
    }
}
