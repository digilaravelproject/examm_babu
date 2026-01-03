<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use App\Models\Skill;
use App\Models\Lesson;
use App\Models\DifficultyLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PracticeLessonController extends Controller
{
    /**
     * STEP 1: Choose Sub Category & Skill
     */
    public function step1()
    {
        $subCategories = SubCategory::select('id', 'name')->get();
        $skills = Skill::select('id', 'name')->get();

        return view('admin.practice_lessons.step1', compact('subCategories', 'skills'));
    }

    /**
     * STEP 2: Manage Lessons (Add/Remove UI)
     */
    public function step2(Request $request)
    {
        $request->validate([
            'sub_category_id' => 'required|exists:sub_categories,id',
            'skill_id' => 'required|exists:skills,id',
        ]);

        $subCategory = SubCategory::findOrFail($request->sub_category_id);
        $skill = Skill::findOrFail($request->skill_id);

        // Fetch Filters
        $difficultyLevels = DifficultyLevel::all();

        // Fetch Lessons based on filters
        $query = Lesson::query();

        if ($request->filled('code')) {
            $query->where('code', 'like', '%' . $request->code . '%');
        }
        if ($request->filled('title')) { // Changed from Topic to Title for better search
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        if ($request->filled('difficulty')) {
            $query->whereIn('difficulty_level_id', $request->difficulty);
        }

        $lessons = $query->latest()->paginate(10)->withQueryString();

        // Get IDs of lessons already attached to this SubCategory + Skill combination
        // Assuming you have a pivot table 'practice_lessons' with 'lesson_id', 'sub_category_id', 'skill_id'
        $attachedLessonIds = DB::table('practice_lessons')
            ->where('sub_category_id', $subCategory->id)
            ->where('skill_id', $skill->id)
            ->pluck('lesson_id')
            ->toArray();

        return view('admin.practice_lessons.step2', compact(
            'subCategory',
            'skill',
            'lessons',
            'attachedLessonIds',
            'difficultyLevels'
        ));
    }

    /**
     * AJAX: Add Lesson
     */
    public function attachLesson(Request $request)
    {
        try {
            $exists = DB::table('practice_lessons')
                ->where('sub_category_id', $request->sub_category_id)
                ->where('skill_id', $request->skill_id)
                ->where('lesson_id', $request->lesson_id)
                ->exists();

            if (!$exists) {
                // Get current max sort order
                $maxOrder = DB::table('practice_lessons')
                    ->where('sub_category_id', $request->sub_category_id)
                    ->where('skill_id', $request->skill_id)
                    ->max('sort_order') ?? 0;

                DB::table('practice_lessons')->insert([
                    'sub_category_id' => $request->sub_category_id,
                    'skill_id' => $request->skill_id,
                    'lesson_id' => $request->lesson_id,
                    'sort_order' => $maxOrder + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Lesson Added']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Remove Lesson
     */
    public function detachLesson(Request $request)
    {
        try {
            DB::table('practice_lessons')
                ->where('sub_category_id', $request->sub_category_id)
                ->where('skill_id', $request->skill_id)
                ->where('lesson_id', $request->lesson_id)
                ->delete();

            return response()->json(['success' => true, 'message' => 'Lesson Removed']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
