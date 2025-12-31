<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubCategoryRequest;
use App\Http\Requests\Admin\UpdateSubCategoryRequest;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\SubCategoryType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File; // Required for public path operations
use Illuminate\Support\Str;

class SubCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = SubCategory::with(['category', 'subCategoryType']);

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
        return view('admin.sub_categories.create', compact('categories', 'types'));
    }

    // --- STORE ---
    public function store(StoreSubCategoryRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            // 1. Auto Generate Code
            $cat = Category::findOrFail($data['category_id']);
            $prefix = strtoupper(substr($cat->code, 0, 3)) . '-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 3));
            $data['code'] = $prefix . '-' . strtoupper(Str::random(4));

            // 2. Handle Image Upload (Input name: image_path)
            if ($request->hasFile('image_path')) {
                $file = $request->file('image_path');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = 'uploads/subcategories'; // Public folder path

                // Move directly to public folder
                $file->move(public_path($path), $filename);

                // Save path to DB
                $data['image_path'] = $path . '/' . $filename;
            }

            // 3. Handle Checkbox
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            SubCategory::create($data);

            DB::commit();
            return redirect()->route('admin.sub-categories.index')->with('success', 'Sub-Category added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SubCat Store Error: ' . $e->getMessage());
            return back()->with('error', 'Error creating sub-category: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(SubCategory $subCategory)
    {
        $categories = Category::active()->get(['id', 'name']);
        $types = SubCategoryType::all(['id', 'name']);
        return view('admin.sub_categories.edit', compact('subCategory', 'categories', 'types'));
    }

    // --- UPDATE ---
    public function update(UpdateSubCategoryRequest $request, SubCategory $subCategory)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            // 1. Handle Image Update
            if ($request->hasFile('image_path')) {

                // Delete Old Image if exists
                if ($subCategory->image_path && File::exists(public_path($subCategory->image_path))) {
                    File::delete(public_path($subCategory->image_path));
                }

                // Upload New Image
                $file = $request->file('image_path');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = 'uploads/subcategories';

                $file->move(public_path($path), $filename);
                $data['image_path'] = $path . '/' . $filename;
            }

            // 2. Handle Checkbox
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            $subCategory->update($data);

            DB::commit();
            return redirect()->route('admin.sub-categories.index')->with('success', 'Sub-Category updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SubCat Update Error: ' . $e->getMessage());
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    // --- DESTROY ---
    public function destroy(SubCategory $subCategory)
    {
        try {
            // Optional: Check dependencies
            // $subCategory->loadCount(['quizzes', 'exams']);
            // if ($subCategory->quizzes_count > 0) return back()->with('error', 'Cannot delete active sub-category.');

            // 1. Delete Image File
            if ($subCategory->image_path && File::exists(public_path($subCategory->image_path))) {
                File::delete(public_path($subCategory->image_path));
            }

            // 2. Delete Record
            $subCategory->delete();

            return back()->with('success', 'Deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'System error during deletion.');
        }
    }
}
