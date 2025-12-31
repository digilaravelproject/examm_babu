<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File; // File Facade Added
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

    // Store function:
    public function store(StoreCategoryRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 3));
            $data['code'] = $prefix . '-' . strtoupper(Str::random(5));

            // YAHAN CHANGE: Hum check kar rahe hain 'image_path' input ke liye
            if ($request->hasFile('image_path')) {
                $file = $request->file('image_path'); // Yahan bhi 'image_path'
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = 'uploads/categories';

                $file->move(public_path($path), $filename);

                // Database me path save karein
                $data['image_path'] = $path . '/' . $filename;
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

    // Update function ke andar ye logic replace karein:

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated(); // Isme saara form data aa gaya

            // YAHAN CHANGE: Hum check kar rahe hain 'image_path' input ke liye
            if ($request->hasFile('image_path')) {

                // Purani image delete karein
                if ($category->image_path && \Illuminate\Support\Facades\File::exists(public_path($category->image_path))) {
                    \Illuminate\Support\Facades\File::delete(public_path($category->image_path));
                }

                // Nayi Image Upload Karein
                $file = $request->file('image_path'); // Yahan bhi 'image_path'
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = 'uploads/categories';

                $file->move(public_path($path), $filename);

                // Database me path save karein
                $data['image_path'] = $path . '/' . $filename;
            }

            $category->update($data);

            DB::commit();
            return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Update Error: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy(Category $category)
    {
        try {
            // Relationships check
            if ($category->subCategories()->count() > 0) {
                return back()->with('error', 'Cannot delete! Category has linked Sub-Categories.');
            }

            // Delete Image from Public Folder
            if ($category->image_path && File::exists(public_path($category->image_path))) {
                File::delete(public_path($category->image_path));
            }

            $category->delete();
            return back()->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting category.');
        }
    }
}
