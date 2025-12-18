<?php

namespace App\Traits;

use App\Models\SubCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;

trait SyllabusTrait
{
    /**
     * Get the user's selected syllabus (SubCategory).
     * * Logic: Check User DB Preferences -> Check Cookie -> Fallback to null.
     */
    public function selectedSyllabus(): ?SubCategory
    {
        // 1. Get ID from User Preferences (DB) or Cookie
        $syllabusId = $this->preferences->get('selected_syllabus_id')
                      ?? Cookie::get('category_id');

        if (!$syllabusId) {
            return null;
        }

        // 2. Cache the syllabus model to avoid repeated DB queries
        return Cache::remember("user_{$this->id}_syllabus_{$syllabusId}", now()->addHours(24), function () use ($syllabusId) {
            try {
                return SubCategory::find($syllabusId);
            } catch (\Exception $e) {
                return null;
            }
        });
    }

    /**
     * Set/Update the selected syllabus for the user.
     * Persistent storage in DB + Cookie for redundancy.
     */
    public function updateSelectedSyllabus(int|string $syllabusId): void
    {
        // Save to User preferences (Schemaless Attributes)
        $this->preferences->selected_syllabus_id = $syllabusId;
        $this->save();

        // Save to Cookie (valid for 1 year)
        Cookie::queue('category_id', $syllabusId, 525600);

        // Clear existing cache
        Cache::forget("user_{$this->id}_syllabus_{$syllabusId}");
    }

    /**
     * Helper to check if user has selected any syllabus.
     */
    public function hasSelectedSyllabus(): bool
    {
        return !is_null($this->selectedSyllabus());
    }
}
