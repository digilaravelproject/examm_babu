<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SectionController extends Controller
{
    public function index(Request $request)
    {
        // Eager load creator if relationship exists
        $query = Section::with(['creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $sections = $query->latest()->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('admin.sections.partials.table', compact('sections'))->render();
        }

        return view('admin.sections.index', compact('sections'));
    }

    public function create()
    {
        return view('admin.sections.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255|unique:sections,name',
            'short_description' => 'nullable|string',
            'is_active'         => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            // Auto Code Generation
            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 3));
            $data['code'] = 'SEC-' . $prefix . strtoupper(Str::random(3));

            // Assign Creator
            $data['created_by'] = Auth::id();
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            Section::create($data);
            DB::commit();

            // Dynamic Redirect
            $routePrefix = Auth::user()->hasRole('admin') ? 'admin.' : 'panel.';
            $params = Auth::user()->hasRole('admin') ? [] : ['role' => 'instructor'];

            return redirect()->route($routePrefix . 'sections.index', $params)
                ->with('success', 'Section created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Section Store Error: ' . $e->getMessage());
            return back()->with('error', 'Error saving section.')->withInput();
        }
    }

    // --- FIXED EDIT METHOD ---
    public function edit($p1, $p2 = null)
    {
        // 1. Resolve ID
        $id = $p2 ?? $p1;
        $section = ($id instanceof Section) ? $id : Section::findOrFail($id);

        // 2. Authorization Check
        if (!Auth::user()->hasRole('admin') && $section->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action. You can only edit sections you created.');
        }

        return view('admin.sections.edit', compact('section'));
    }

    // --- FIXED UPDATE METHOD ---
    public function update(Request $request, $p1, $p2 = null)
    {
        // 1. Resolve ID
        $id = $p2 ?? $p1;
        $section = ($id instanceof Section) ? $id : Section::findOrFail($id);

        // 2. Authorization Check
        if (!Auth::user()->hasRole('admin') && $section->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'name'              => 'required|string|max:255|unique:sections,name,' . $section->id,
            'short_description' => 'nullable|string',
            'is_active'         => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            $section->update($data);
            DB::commit();

            $routePrefix = Auth::user()->hasRole('admin') ? 'admin.' : 'panel.';
            $params = Auth::user()->hasRole('admin') ? [] : ['role' => 'instructor'];

            return redirect()->route($routePrefix . 'sections.index', $params)
                ->with('success', 'Section updated successfully!');

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
        $section = ($id instanceof Section) ? $id : Section::findOrFail($id);

        // 2. Authorization Check
        if (!Auth::user()->hasRole('admin') && $section->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            // Check dependencies
            $section->loadCount(['skills', 'examSections']); // Assuming these relations exist
            if (($section->skills_count ?? 0) > 0 || ($section->exam_sections_count ?? 0) > 0) {
                return back()->with('error', 'Cannot delete! This section is linked to skills or exams.');
            }

            // Detach Pivot if exists
            if (method_exists($section, 'subCategories')) {
                $section->subCategories()->detach();
            }

            $section->delete();
            DB::commit();
            return back()->with('success', 'Section deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Deletion error: ' . $e->getMessage());
        }
    }
}
