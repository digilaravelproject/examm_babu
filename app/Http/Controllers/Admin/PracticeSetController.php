<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PracticeSet;
use App\Models\SubCategory;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PracticeSetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PracticeSet::with(['subCategory', 'skill']);

        // 1. Search Filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('code', 'like', '%' . $searchTerm . '%');
            });
        }

        // 2. Sub Category Filter
        if ($request->filled('sub_category_id')) {
            $query->where('sub_category_id', $request->sub_category_id);
        }

        // 3. Skill Filter
        if ($request->filled('skill_id')) {
            $query->where('skill_id', $request->skill_id);
        }

        // withQueryString() Bahut Zaroori hai Pagination ke liye
        $practiceSets = $query->latest()->paginate(10)->withQueryString();

        // ★★★ AJAX CHECK ★★★
        if ($request->ajax()) {
            return view('admin.practice_sets.partials.table', compact('practiceSets'))->render();
        }

        // Normal Load
        $subCategories = SubCategory::select('id', 'name')->get();
        $skills = Skill::select('id', 'name')->get();

        return view('admin.practice_sets.index', compact('practiceSets', 'subCategories', 'skills'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $subCategories = SubCategory::select('id', 'name')->get();
        $skills = Skill::select('id', 'name')->get();

        return view('admin.practice_sets.create', compact('subCategories', 'skills'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'skill_id' => 'required|exists:skills,id',
            'description' => 'nullable|string',
            'is_paid' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Auto-generate code if not provided
        $code = strtoupper(Str::random(8));

        $practiceSet = PracticeSet::create([
            'title' => $request->title,
            'code' => $code, // Ensure your model fillable has 'code'
            'slug' => Str::slug($request->title),
            'sub_category_id' => $request->sub_category_id,
            'skill_id' => $request->skill_id,
            'description' => $request->description,
            'is_paid' => $request->is_paid ?? 0,
            'is_active' => $request->is_active ?? 1,
            // 'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.practice-sets.settings', $practiceSet->id)
            ->with('success', 'Practice Set created successfully! Configure settings now.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $practiceSet = PracticeSet::findOrFail($id);
        $subCategories = SubCategory::select('id', 'name')->get();
        $skills = Skill::select('id', 'name')->get();

        return view('admin.practice_sets.edit', compact('practiceSet', 'subCategories', 'skills'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'skill_id' => 'required|exists:skills,id',
            'is_paid' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $practiceSet = PracticeSet::findOrFail($id);
        $practiceSet->update([
            'title' => $request->title,
            'sub_category_id' => $request->sub_category_id,
            'skill_id' => $request->skill_id,
            'description' => $request->description,
            'is_paid' => $request->is_paid ?? 0,
            'is_active' => $request->is_active ?? 1,
        ]);

        return redirect()->route('admin.practice-sets.settings', $practiceSet->id)
            ->with('success', 'Details updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $practiceSet = PracticeSet::findOrFail($id);
        $practiceSet->delete();

        return redirect()->route('admin.practice-sets.index')
            ->with('success', 'Practice Set deleted successfully.');
    }

    /**
     * Show Settings Page
     */
    public function settings($id)
    {
        $practiceSet = PracticeSet::findOrFail($id);
        return view('admin.practice_sets.settings', compact('practiceSet'));
    }

    /**
     * Update Settings
     */
    public function updateSettings(Request $request, $id)
    {
        $request->validate([
            'allow_rewards' => 'boolean',
            'auto_grading' => 'boolean',
            'correct_marks' => 'nullable|numeric',
            'show_reward_popup' => 'boolean',
        ]);

        $practiceSet = PracticeSet::findOrFail($id);

        $practiceSet->allow_rewards = $request->allow_rewards ?? 0;
        $practiceSet->auto_grading = $request->auto_grading ?? 0;

        // If auto grading is OFF, require marks
        if (!$practiceSet->auto_grading && !$request->correct_marks) {
            return back()->withErrors(['correct_marks' => 'Marks are required for manual grading.']);
        }

        $practiceSet->correct_marks = $request->correct_marks ?? 1;

        // Save settings JSON column if your model uses schemaless attributes
        // Assuming 'settings' column exists and cast to array in model
        $currentSettings = $practiceSet->settings ?? [];
        $currentSettings['show_reward_popup'] = $request->has('show_reward_popup');
        $practiceSet->settings = $currentSettings;

        $practiceSet->save();

        return redirect()->route('admin.practice-sets.index')
            ->with('success', 'Settings updated successfully!');
    }
}
