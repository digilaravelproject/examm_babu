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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = SubCategory::with(['category', 'subCategoryType']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
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
        return view('admin.sub_categories.create', compact('categories', 'types'));
    }

    public function store(StoreSubCategoryRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            // Auto Code Generation
            $cat = Category::findOrFail($data['category_id']);
            $prefix = strtoupper(substr($cat->code, 0, 3)) . '-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 3));
            $data['code'] = $prefix . '-' . strtoupper(Str::random(4));

            if ($request->hasFile('image')) {
                $data['image_path'] = $request->file('image')->store('subcategories', 'public');
            }

            SubCategory::create($data);
            DB::commit();
            return redirect()->route('admin.sub-categories.index')->with('success', 'Sub-Category added!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return back()->with('error', 'Error creating sub-category.')->withInput();
        }
    }

    public function edit(SubCategory $subCategory)
    {
        $categories = Category::active()->get(['id', 'name']);
        $types = SubCategoryType::all(['id', 'name']);
        return view('admin.sub_categories.edit', compact('subCategory', 'categories', 'types'));
    }

    public function update(UpdateSubCategoryRequest $request, SubCategory $subCategory)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            if ($request->hasFile('image')) {
                if ($subCategory->image_path) Storage::disk('public')->delete($subCategory->image_path);
                $data['image_path'] = $request->file('image')->store('subcategories', 'public');
            }
            $subCategory->update($data);
            DB::commit();
            return redirect()->route('admin.sub-categories.index')->with('success', 'Sub-Category updated!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Update failed.');
        }
    }

    public function destroy(SubCategory $subCategory)
    {
        try {
            $subCategory->loadCount(['quizzes', 'exams', 'plans']);
            if ($subCategory->quizzes_count > 0 || $subCategory->exams_count > 0) {
                return back()->with('error', 'Cannot delete! Records are associated.');
            }
            if ($subCategory->image_path) Storage::disk('public')->delete($subCategory->image_path);
            $subCategory->delete();
            return back()->with('success', 'Deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'System error during deletion.');
        }
    }
}
