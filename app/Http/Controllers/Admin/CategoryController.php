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

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Category::query();

        // Direct Filtering Logic inside Controller
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%"); // Fixed here
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $categories = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            // Handle Image Upload
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('categories', 'public');
                $data['image_path'] = $path;
            }

            Category::create($data);

            DB::commit();
            return redirect()->route('admin.categories.index')
                ->with('success', 'Category created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Category Store Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong! Please try again.')->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        // return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            // Handle Image Upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($category->image_path && Storage::disk('public')->exists($category->image_path)) {
                    Storage::disk('public')->delete($category->image_path);
                }
                $path = $request->file('image')->store('categories', 'public');
                $data['image_path'] = $path;
            }

            $category->update($data);

            DB::commit();
            return redirect()->route('admin.categories.index')
                ->with('success', 'Category updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Category Update Error: ' . $e->getMessage());
            return back()->with('error', 'Unable to update category.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        DB::beginTransaction();
        try {
            // Enhanced Association Check using Eloquent Counts
            $category->loadCount(['subCategories', 'plans']); // Assuming relationships exist in Model

            if ($category->sub_categories_count > 0 || $category->plans_count > 0) {
                return back()->with('error', 'Cannot delete! This category has active Sub-Categories or Plans linked to it.');
            }

            // Delete Image
            if ($category->image_path && Storage::disk('public')->exists($category->image_path)) {
                Storage::disk('public')->delete($category->image_path);
            }

            $category->delete();

            DB::commit();
            return back()->with('success', 'Category deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Category Delete Error: ' . $e->getMessage());
            return back()->with('error', 'System error while deleting category.');
        }
    }
}
