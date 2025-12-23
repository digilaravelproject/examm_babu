<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSectionRequest;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Section::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
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

    public function store(StoreSectionRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            // Auto Code Generation (e.g., SEC-QT12)
            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 3));
            $data['code'] = 'SEC-' . $prefix . strtoupper(Str::random(3));

            Section::create($data);
            DB::commit();
            return redirect()->route('admin.sections.index')->with('success', 'Section created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return back()->with('error', 'Error saving section.')->withInput();
        }
    }

    public function edit(Section $section)
    {
        return view('admin.sections.edit', compact('section'));
    }

    public function update(StoreSectionRequest $request, Section $section)
    {
        DB::beginTransaction();
        try {
            $section->update($request->validated());
            DB::commit();
            return redirect()->route('admin.sections.index')->with('success', 'Section updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Update failed.');
        }
    }

    public function destroy(Section $section)
    {
        DB::beginTransaction();
        try {
            $section->loadCount(['skills', 'examSections']);
            if ($section->skills_count > 0 || $section->exam_sections_count > 0) {
                return back()->with('error', 'Cannot delete! This section is linked to skills or exams.');
            }
            $section->subCategories()->detach();
            $section->delete();
            DB::commit();
            return back()->with('success', 'Section deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Deletion error.');
        }
    }
}
