<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TagController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware(['role:admin|instructor'])->except('search');
    // }

    public function index(Request $request)
    {
        $query = Tag::query();

        // Search Filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $tags = $query->latest()->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('admin.tags.partials.table', compact('tags'))->render();
        }

        return view('admin.tags.index', compact('tags'));
    }

    public function search(Request $request)
    {
        $query = $request->get('query');
        $tags = Tag::select(['id', 'name'])
            ->where('name', 'like', '%' . $query . '%')
            ->limit(20)
            ->get();

        return response()->json(['tags' => $tags]);
    }

    public function create()
    {
        return view('admin.tags.create');
    }

    public function store(StoreTagRequest $request)
    {
        try {
            Tag::create($request->validated());
            return redirect()->route('admin.tags.index')->with('success', 'Tag created successfully!');
        } catch (\Exception $e) {
            Log::error("Tag Store Error: " . $e->getMessage());
            return back()->with('error', 'Failed to save tag.')->withInput();
        }
    }

    public function edit(Tag $tag)
    {
        return view('admin.tags.edit', compact('tag'));
    }

    public function update(StoreTagRequest $request, Tag $tag)
    {
        try {
            $tag->update($request->validated());
            return redirect()->route('admin.tags.index')->with('success', 'Tag updated successfully!');
        } catch (\Exception $e) {
            Log::error("Tag Update Error: " . $e->getMessage());
            return back()->with('error', 'Update failed.');
        }
    }

    public function destroy(Tag $tag)
    {
        DB::beginTransaction();
        try {
            // Detach relationships
            $tag->questions()->detach();
            $tag->lessons()->detach();
            $tag->videos()->detach();

            $tag->delete();

            DB::commit();
            return back()->with('success', 'Tag deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Tag Delete Error: " . $e->getMessage());
            return back()->with('error', 'Unable to delete tag due to associations.');
        }
    }
}
