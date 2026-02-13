<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TopicController extends Controller
{
    public function index(Request $request)
    {
        // Load relationships including creator and nested category hierarchy
        $query = Topic::with([
            'skill:id,name,micro_category_id',
            'skill.microCategory:id,name,sub_category_id',
            'skill.microCategory.subCategory:id,name',
            'creator'
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('skill_id')) {
            $query->where('skill_id', $request->skill_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $topics = $query->latest()->paginate(10)->withQueryString();
        $skills = Skill::active()->get(['id', 'name']);

        if ($request->ajax()) {
            return view('admin.topics.partials.table', compact('topics'))->render();
        }

        return view('admin.topics.index', compact('topics', 'skills'));
    }

    public function create()
    {
        $skills = Skill::active()->get(['id', 'name']);
        return view('admin.topics.create', compact('skills'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'skill_id'          => 'required|exists:skills,id',
            'name'              => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'is_active'         => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            // Auto Code Generation
            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 3));
            $data['code'] = 'TOP-' . $prefix . strtoupper(Str::random(3));

            // Assign Creator
            $data['created_by'] = Auth::id();
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            Topic::create($data);
            DB::commit();

            // Dynamic Redirect
            $routePrefix = Auth::user()->hasRole('admin') ? 'admin.' : 'panel.';
            $params = Auth::user()->hasRole('admin') ? [] : ['role' => 'instructor'];

            return redirect()->route($routePrefix . 'topics.index', $params)
                ->with('success', 'Topic created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Topic Store Error: ' . $e->getMessage());
            return back()->with('error', 'Error saving topic.')->withInput();
        }
    }

    // --- FIXED EDIT METHOD ---
    public function edit($p1, $p2 = null)
    {
        // 1. Resolve ID
        $id = $p2 ?? $p1;
        $topic = ($id instanceof Topic) ? $id : Topic::findOrFail($id);

        // 2. Authorization Check
        if (!Auth::user()->hasRole('admin') && $topic->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action. You can only edit topics you created.');
        }

        $skills = Skill::active()->get(['id', 'name']);
        return view('admin.topics.edit', compact('topic', 'skills'));
    }

    // --- FIXED UPDATE METHOD ---
    public function update(Request $request, $p1, $p2 = null)
    {
        // 1. Resolve ID
        $id = $p2 ?? $p1;
        $topic = ($id instanceof Topic) ? $id : Topic::findOrFail($id);

        // 2. Authorization Check
        if (!Auth::user()->hasRole('admin') && $topic->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'skill_id'          => 'required|exists:skills,id',
            'name'              => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'is_active'         => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            $topic->update($data);
            DB::commit();

            $routePrefix = Auth::user()->hasRole('admin') ? 'admin.' : 'panel.';
            $params = Auth::user()->hasRole('admin') ? [] : ['role' => 'instructor'];

            return redirect()->route($routePrefix . 'topics.index', $params)
                ->with('success', 'Topic updated successfully!');

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
        $topic = ($id instanceof Topic) ? $id : Topic::findOrFail($id);

        // 2. Authorization Check
        if (!Auth::user()->hasRole('admin') && $topic->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            // Check dependencies if any (e.g., questions)
            // if ($topic->questions()->count() > 0) { ... }

            $topic->delete();
            DB::commit();
            return back()->with('success', 'Topic deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Unable to delete topic.');
        }
    }
}
