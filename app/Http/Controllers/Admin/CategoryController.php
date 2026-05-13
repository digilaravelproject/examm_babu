<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

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

        // Added withCount to easily check dependencies in the view
        $categories = $query->with(['creator'])
            ->withCount('subCategories')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

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
            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 3));
            $data['code'] = $prefix . '-' . strtoupper(Str::random(5));
            $data['created_by'] = Auth::id();

            if ($request->hasFile('image_path')) {
                $file = $request->file('image_path');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = 'uploads/categories';
                $file->move(public_path($path), $filename);
                $data['image_path'] = $path . '/' . $filename;
            }

            Category::create($data);
            DB::commit();

            $routePrefix = Auth::user()->hasRole('admin') ? 'admin.' : 'panel.';
            $params = Auth::user()->hasRole('admin') ? [] : ['role' => 'instructor'];

            return redirect()->route($routePrefix . 'categories.index', $params)->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Category Store Error: ' . $e->getMessage());
            return back()->with('error', 'Unable to create category.')->withInput();
        }
    }

    public function edit($p1, $p2 = null)
    {
        $id = $p2 ?? $p1;
        $category = ($id instanceof Category) ? $id : Category::findOrFail($id);

        if (!Auth::user()->hasRole('admin') && $category->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, $p1, $p2 = null)
    {
        $id = $p2 ?? $p1;
        $category = ($id instanceof Category) ? $id : Category::findOrFail($id);

        if (!Auth::user()->hasRole('admin') && $category->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            $data = $request->validated();

            if ($request->hasFile('image_path')) {
                if ($category->image_path && File::exists(public_path($category->image_path))) {
                    File::delete(public_path($category->image_path));
                }
                $file = $request->file('image_path');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = 'uploads/categories';
                $file->move(public_path($path), $filename);
                $data['image_path'] = $path . '/' . $filename;
            }

            $category->update($data);
            DB::commit();

            $routePrefix = Auth::user()->hasRole('admin') ? 'admin.' : 'panel.';
            $params = Auth::user()->hasRole('admin') ? [] : ['role' => 'instructor'];

            return redirect()->route($routePrefix . 'categories.index', $params)->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Error: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // --- UPDATED DESTROY METHOD (Strict Hard Delete) ---
    public function destroy($p1, $p2 = null)
    {
        $id = $p2 ?? $p1;
        $category = ($id instanceof Category) ? $id : Category::findOrFail($id);

        if (!Auth::user()->hasRole('admin') && $category->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            // 1. Strict Check: Do not delete if SubCategories exist
            if ($category->subCategories()->count() > 0) {
                return back()->with('error', 'Action Blocked: This category has linked Sub-Categories. Please delete them first.');
            }

            // 2. Delete Category Image if exists
            if ($category->image_path && File::exists(public_path($category->image_path))) {
                File::delete(public_path($category->image_path));
            }

            // 3. Hard Delete the Category
            $category->forceDelete();

            DB::commit();
            return back()->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
