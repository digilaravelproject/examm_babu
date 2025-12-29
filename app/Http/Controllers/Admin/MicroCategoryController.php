<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use App\Models\MicroCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MicroCategoryController extends Controller
{
    public function index(Request $request)
    {
        // Relationship load ki: SubCategory aur uski parent Category
        $query = MicroCategory::with(['subCategory.category']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('sub_category_id')) {
            $query->where('sub_category_id', $request->sub_category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $microCategories = $query->latest()->paginate(10)->withQueryString();

        // Dropdown ke liye data (Category name ke sath taaki clear ho)
        $subCategories = SubCategory::active()->with('category')->get();

        // AJAX Request ke liye sirf Table return karega
        if ($request->ajax()) {
            return view('admin.micro_categories.partials.table', compact('microCategories'))->render();
        }

        return view('admin.micro_categories.index', compact('microCategories', 'subCategories'));
    }

    public function create()
    {
        $subCategories = SubCategory::active()->with('category')->get();
        return view('admin.micro_categories.create', compact('subCategories'));
    }

    public function store(Request $request)
    {
        // Validation Yahi Par
        $data = $request->validate([
            'sub_category_id' => 'required|exists:sub_categories,id',
            'name'            => 'required|string|max:255',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active'       => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            // Auto Code Generation: SUBCAT-MICRO-RANDOM
            $subCat = SubCategory::findOrFail($data['sub_category_id']);
            $prefix = strtoupper(substr($subCat->code, 0, 3)) . '-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 3));
            $data['code'] = $prefix . '-' . strtoupper(Str::random(4));

            if ($request->hasFile('image')) {
                $data['image_path'] = $request->file('image')->store('micro_categories', 'public');
            }

            // Checkbox handling
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            MicroCategory::create($data);
            DB::commit();
            return redirect()->route('admin.micro-categories.index')->with('success', 'Micro-Category added!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return back()->with('error', 'Error creating micro-category.')->withInput();
        }
    }

    public function edit(MicroCategory $microCategory)
    {
        $subCategories = SubCategory::active()->with('category')->get();
        return view('admin.micro_categories.edit', compact('microCategory', 'subCategories'));
    }

    public function update(Request $request, MicroCategory $microCategory)
    {
        $data = $request->validate([
            'sub_category_id' => 'required|exists:sub_categories,id',
            'name'            => 'required|string|max:255',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active'       => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            if ($request->hasFile('image')) {
                if ($microCategory->image_path) Storage::disk('public')->delete($microCategory->image_path);
                $data['image_path'] = $request->file('image')->store('micro_categories', 'public');
            }

            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            $microCategory->update($data);
            DB::commit();
            return redirect()->route('admin.micro-categories.index')->with('success', 'Micro-Category updated!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Update failed.');
        }
    }

    public function destroy(MicroCategory $microCategory)
    {
        try {
            if ($microCategory->image_path) Storage::disk('public')->delete($microCategory->image_path);
            $microCategory->delete();
            return back()->with('success', 'Deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'System error during deletion.');
        }
    }
}
