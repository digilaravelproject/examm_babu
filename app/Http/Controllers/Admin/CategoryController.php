<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $categories = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('admin.categories.partials.table', compact('categories'))->render();
        }

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            // --- AUTO GENERATE CODE ---
            // Name ke first 3 letters + Random Unique String (e.g., HIS-A12B)
            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 3));
            $data['code'] = $prefix . '-' . strtoupper(Str::random(5));

            if ($request->hasFile('image')) {
                $data['image_path'] = $request->file('image')->store('categories', 'public');
            }

            Category::create($data);

            DB::commit();
            return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Category Store Error: ' . $e->getMessage());
            return back()->with('error', 'Unable to create category.')->withInput();
        }
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                if ($category->image_path && Storage::disk('public')->exists($category->image_path)) {
                    Storage::disk('public')->delete($category->image_path);
                }
                $data['image_path'] = $request->file('image')->store('categories', 'public');
            }

            $category->update($data);

            DB::commit();
            return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Category Update Error: ' . $e->getMessage());
            return back()->with('error', 'Unable to update category.')->withInput();
        }
    }

    public function destroy(Category $category)
    {
        try {
            // Relationships check
            if ($category->subCategories()->count() > 0) {
                return back()->with('error', 'Cannot delete! Category has linked Sub-Categories.');
            }

            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }

            $category->delete();
            return back()->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting category.');
        }
    }
}
