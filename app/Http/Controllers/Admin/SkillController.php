<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSkillRequest;
use App\Http\Requests\Admin\UpdateSkillRequest;
use App\Models\Section;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SkillController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware(['role:admin|instructor'])->except('search');
    // }

    public function index(Request $request)
    {
        $query = Skill::with('section:id,name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
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

    public function store(StoreSkillRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            // Auto Code Generation (e.g., SKL-NAM12)
            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 3));
            $data['code'] = 'SKL-' . $prefix . strtoupper(Str::random(3));

            Skill::create($data);
            DB::commit();
            return redirect()->route('admin.skills.index')->with('success', 'Skill added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return back()->with('error', 'Error saving skill.')->withInput();
        }
    }

    public function edit(Skill $skill)
    {
        $sections = Section::active()->get(['id', 'name']);
        return view('admin.skills.edit', compact('skill', 'sections'));
    }

    public function update(StoreSkillRequest $request, Skill $skill)
    {
        DB::beginTransaction();
        try {
            $skill->update($request->validated());
            DB::commit();
            return redirect()->route('admin.skills.index')->with('success', 'Skill updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Update failed.');
        }
    }

    public function destroy(Skill $skill)
    {
        DB::beginTransaction();
        try {
            $skill->loadCount(['topics', 'questions', 'practiceSets', 'lessons', 'videos']);

            if ($skill->topics_count > 0 || $skill->questions_count > 0 || $skill->practice_sets_count > 0) {
                return back()->with('error', 'Cannot delete! This skill has associated data.');
            }

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
