<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SkillController extends Controller
{
    public function index(Request $request)
    {
        // Load relationships including creator for the table view
        $query = Skill::with(['section:id,name', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $skills = $query->orderBy('name')->paginate(10)->withQueryString();
        $sections = Section::active()->get(['id', 'name']);

        if ($request->ajax()) {
            return view('admin.skills.partials.table', compact('skills'))->render();
        }

        return view('admin.skills.index', compact('skills', 'sections'));
    }

    public function create()
    {
        $sections = Section::active()->get(['id', 'name']);
        return view('admin.skills.create', compact('sections'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'section_id'        => 'required|exists:sections,id',
            'name'              => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'is_active'         => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            // Auto Code Generation
            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 3));
            $data['code'] = 'SKL-' . $prefix . strtoupper(Str::random(3));

            // Assign Creator
            $data['created_by'] = Auth::id();
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            Skill::create($data);
            DB::commit();

            // Dynamic Redirect
            $routePrefix = Auth::user()->hasRole('admin') ? 'admin.' : 'panel.';
            $params = Auth::user()->hasRole('admin') ? [] : ['role' => 'instructor'];

            return redirect()->route($routePrefix . 'skills.index', $params)
                ->with('success', 'Skill added successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Skill Store Error: ' . $e->getMessage());
            return back()->with('error', 'Error saving skill.')->withInput();
        }
    }

    // --- FIXED EDIT METHOD ---
    public function edit($p1, $p2 = null)
    {
        // 1. Resolve ID
        $id = $p2 ?? $p1;
        $skill = ($id instanceof Skill) ? $id : Skill::findOrFail($id);

        // 2. Authorization Check
        if (!Auth::user()->hasRole('admin') && $skill->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action. You can only edit skills you created.');
        }

        $sections = Section::active()->get(['id', 'name']);
        return view('admin.skills.edit', compact('skill', 'sections'));
    }

    // --- FIXED UPDATE METHOD ---
    public function update(Request $request, $p1, $p2 = null)
    {
        // 1. Resolve ID
        $id = $p2 ?? $p1;
        $skill = ($id instanceof Skill) ? $id : Skill::findOrFail($id);

        // 2. Authorization Check
        if (!Auth::user()->hasRole('admin') && $skill->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'section_id'        => 'required|exists:sections,id',
            'name'              => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'is_active'         => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            $skill->update($data);
            DB::commit();

            $routePrefix = Auth::user()->hasRole('admin') ? 'admin.' : 'panel.';
            $params = Auth::user()->hasRole('admin') ? [] : ['role' => 'instructor'];

            return redirect()->route($routePrefix . 'skills.index', $params)
                ->with('success', 'Skill updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Update failed.');
        }
    }

    // --- FIXED DESTROY METHOD ---
    public function destroy($p1, $p2 = null)
    {
        // 1. Resolve ID
        $id = $p2 ?? $p1;
        $skill = ($id instanceof Skill) ? $id : Skill::findOrFail($id);

        // 2. Authorization Check
        if (!Auth::user()->hasRole('admin') && $skill->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            // Check dependencies
            $skill->loadCount(['topics', 'questions', 'practiceSets']);

            if ($skill->topics_count > 0 || $skill->questions_count > 0 || $skill->practice_sets_count > 0) {
                return back()->with('error', 'Cannot delete! This skill has associated data.');
            }

            // Detach pivots
            $skill->practiceLessons()->detach();
            $skill->practiceVideos()->detach();

            $skill->delete();
            DB::commit();
            return back()->with('success', 'Skill deleted.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Deletion error.');
        }
    }
}
