<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Skill;
use App\Models\Topic;
use App\Models\DifficultyLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
    public function index(Request $request)
    {
        // Eager load relationships for table
        $query = Lesson::with(['skill', 'topic', 'difficultyLevel', 'section']);

        // 1. Search Filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        // 2. Skill Filter
        if ($request->filled('skill_id')) {
            $query->where('skill_id', $request->skill_id);
        }

        // 3. Status Filter
        if ($request->filled('status')) {
            $isActive = $request->status == 'active' ? 1 : 0;
            $query->where('is_active', $isActive);
        }

        $lessons = $query->latest()->paginate(10)->withQueryString();

        // AJAX Request: Return only Table Partial
        if ($request->ajax()) {
            return view('admin.lessons.partials.lessons-table', compact('lessons'))->render();
        }

        // Normal Request: Return Full Page
        $skills = Skill::where('is_active', 1)->select('id', 'name')->get();

        return view('admin.lessons.index', compact('lessons', 'skills'));
    }

    public function create()
    {
        $skills = Skill::where('is_active', 1)->get();
        $topics = Topic::where('is_active', 1)->get();
        $difficultyLevels = DifficultyLevel::all();

        return view('admin.lessons.create', compact('skills', 'topics', 'difficultyLevels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required',
            'skill_id' => 'required|exists:skills,id',
            'difficulty_level_id' => 'required|exists:difficulty_levels,id',
            'duration' => 'required|numeric|min:1',
        ]);

        $data = $request->all();
        $data['code'] = 'LSN_' . strtoupper(Str::random(8));
        $data['slug'] = Str::slug($request->title);
        $data['created_by'] = Auth::id();
        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['is_paid'] = $request->has('is_paid') ? 1 : 0;

        Lesson::create($data);

        return redirect()->route('admin.lessons.index')->with('success', 'Lesson created successfully.');
    }

    public function edit($id)
    {
        $lesson = Lesson::findOrFail($id);
        $skills = Skill::where('is_active', 1)->get();
        $topics = Topic::where('is_active', 1)->where('skill_id', $lesson->skill_id)->get();
        $difficultyLevels = DifficultyLevel::all();

        return view('admin.lessons.edit', compact('lesson', 'skills', 'topics', 'difficultyLevels'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required',
            'skill_id' => 'required',
            'difficulty_level_id' => 'required',
            'duration' => 'required|numeric',
        ]);

        $lesson = Lesson::findOrFail($id);

        $data = $request->all();
        $data['slug'] = Str::slug($request->title);
        $data['is_active'] = $request->has('is_active') ? 1 : 0;
        $data['is_paid'] = $request->has('is_paid') ? 1 : 0;

        $lesson->update($data);

        return redirect()->route('admin.lessons.index')->with('success', 'Lesson updated successfully.');
    }

    public function destroy($id)
    {
        $lesson = Lesson::findOrFail($id);
        $lesson->delete();
        return back()->with('success', 'Lesson deleted successfully.');
    }

    // --- BULK DESTROY ---
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:lessons,id'
        ]);

        try {
            $count = count($request->ids);
            Lesson::whereIn('id', $request->ids)->delete();
            return response()->json(['success' => true, 'message' => "{$count} lessons deleted successfully."]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting items.'], 500);
        }
    }
}
