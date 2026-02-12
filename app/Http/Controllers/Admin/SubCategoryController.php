<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubCategoryRequest;
use App\Http\Requests\Admin\UpdateSubCategoryRequest;
use App\Models\Category;
use App\Models\Section;
use App\Models\SubCategory;
use App\Models\SubCategoryType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SubCategoryController extends Controller
{
    public function index(Request $request)
    {
        // Added withCount('microCategories') to check dependencies in view
        $query = SubCategory::with(['category', 'subCategoryType', 'creator'])
            ->withCount('microCategories');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $subCategories = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::active()->get(['id', 'name']);

        if ($request->ajax()) {
            return view('admin.sub_categories.partials.table', compact('subCategories'))->render();
        }

        return view('admin.sub_categories.index', compact('subCategories', 'categories'));
    }

    public function create()
    {
        $categories = Category::active()->get(['id', 'name', 'code']);
        $types = SubCategoryType::all(['id', 'name']);
        $subCategory = new SubCategory();

        return view('admin.sub_categories.create', compact('categories', 'types', 'subCategory'));
    }

    public function store(StoreSubCategoryRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            $cat = Category::findOrFail($data['category_id']);
            $prefix = strtoupper(substr($cat->code, 0, 3)) . '-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 3));
            $data['code'] = $prefix . '-' . strtoupper(Str::random(4));
            $data['created_by'] = Auth::id();

            if ($request->hasFile('image_path')) {
                $file = $request->file('image_path');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = 'uploads/subcategories';
                $file->move(public_path($path), $filename);
                $data['image_path'] = $path . '/' . $filename;
            }

            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            SubCategory::create($data);
            DB::commit();

            $routePrefix = Auth::user()->hasRole('admin') ? 'admin.' : 'panel.';
            $params = Auth::user()->hasRole('admin') ? [] : ['role' => 'instructor'];

            return redirect()->route($routePrefix . 'sub-categories.index', $params)->with('success', 'Sub-Category added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SubCat Store Error: ' . $e->getMessage());
            return back()->with('error', 'Error creating sub-category: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($p1, $p2 = null)
    {
        $id = $p2 ?? $p1;
        $subCategory = ($id instanceof SubCategory) ? $id : SubCategory::findOrFail($id);

        if (!Auth::user()->hasRole('admin') && $subCategory->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $categories = Category::active()->get(['id', 'name']);
        $types = SubCategoryType::all(['id', 'name']);
        return view('admin.sub_categories.edit', compact('subCategory', 'categories', 'types'));
    }

    public function update(UpdateSubCategoryRequest $request, $p1, $p2 = null)
    {
        $id = $p2 ?? $p1;
        $subCategory = ($id instanceof SubCategory) ? $id : SubCategory::findOrFail($id);

        if (!Auth::user()->hasRole('admin') && $subCategory->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            $data = $request->validated();

            $subCategory->category_id = $data['category_id'];
            $subCategory->sub_category_type_id = $data['sub_category_type_id'];
            $subCategory->name = $data['name'];
            $subCategory->short_description = $data['short_description'] ?? null;

            if ($request->hasFile('image_path')) {
                if ($subCategory->image_path && File::exists(public_path($subCategory->image_path))) {
                    File::delete(public_path($subCategory->image_path));
                }
                $file = $request->file('image_path');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = 'uploads/subcategories';
                $file->move(public_path($path), $filename);
                $subCategory->image_path = $path . '/' . $filename;
            }

            // $subCategory->is_active = $request->has('is_active') ? 1 : 0;
            $subCategory->is_active = $data['is_active'];
            $subCategory->save();

            DB::commit();

            $routePrefix = Auth::user()->hasRole('admin') ? 'admin.' : 'panel.';
            $params = Auth::user()->hasRole('admin') ? [] : ['role' => 'instructor'];

            return redirect()->route($routePrefix . 'sub-categories.index', $params)->with('success', 'Sub-Category updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SubCat Update Error: ' . $e->getMessage());
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    // --- UPDATED DESTROY METHOD (Strict Check) ---
    public function destroy($p1, $p2 = null)
    {
        $id = $p2 ?? $p1;
        $subCategory = ($id instanceof SubCategory) ? $id : SubCategory::findOrFail($id);

        if (!Auth::user()->hasRole('admin') && $subCategory->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // 1. Check for Dependencies (Micro Categories)
            if ($subCategory->microCategories()->count() > 0) {
                return back()->with('error', 'Action Blocked: This sub-category has linked Micro-Categories. Please delete them first.');
            }

            // 2. Delete Image
            if ($subCategory->image_path && File::exists(public_path($subCategory->image_path))) {
                File::delete(public_path($subCategory->image_path));
            }

            // 3. Hard Delete
            $subCategory->forceDelete();

            return back()->with('success', 'Deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'System error during deletion.');
        }
    }

    // --- SECTIONS MAPPING ---
    public function fetchSections($p1, $p2 = null)
    {
        $id = $p2 ?? $p1;
        $subCategory = SubCategory::with('sections')->findOrFail($id);
        $allSections = Section::where('is_active', 1)->get(['id', 'name', 'code']);
        return view('admin.sub_categories.partials.mapping', compact('subCategory', 'allSections'))->render();
    }

    public function updateSections(Request $request, $p1, $p2 = null)
    {
        $id = $p2 ?? $p1;
        $subCategory = SubCategory::findOrFail($id);

        if (!Auth::user()->hasRole('admin') && $subCategory->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $subCategory->sections()->sync($request->sections ?? []);
        return back()->with('success', 'Sections mapped successfully!');
    }
}
