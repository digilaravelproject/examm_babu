<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTopicRequest;
use App\Http\Requests\Admin\UpdateTopicRequest;
use App\Models\Skill;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TopicController extends Controller
{
    public function index(Request $request)
    {
        $query = Topic::with('skill:id,name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
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

    public function store(StoreTopicRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            // Auto Code Generation (e.g., TOP-TRG12)
            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 3));
            $data['code'] = 'TOP-' . $prefix . strtoupper(Str::random(3));

            Topic::create($data);
            DB::commit();
            return redirect()->route('admin.topics.index')->with('success', 'Topic created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return back()->with('error', 'Error saving topic.')->withInput();
        }
    }

    public function edit(Topic $topic)
    {
        $skills = Skill::active()->get(['id', 'name']);
        return view('admin.topics.edit', compact('topic', 'skills'));
    }

    public function update(StoreTopicRequest $request, Topic $topic)
    {
        DB::beginTransaction();
        try {
            $topic->update($request->validated());
            DB::commit();
            return redirect()->route('admin.topics.index')->with('success', 'Topic updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Update failed.');
        }
    }

    public function destroy(Topic $topic)
    {
        DB::beginTransaction();
        try {
            // Force delete as per your original controller logic,
            // but usually association check is better.
            $topic->forceDelete();
            DB::commit();
            return back()->with('success', 'Topic deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Unable to delete topic. Association exists.');
        }
    }
}
