<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use App\Models\MicroCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MicroCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = MicroCategory::with(['subCategory.category', 'creator']);

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // SubCategory Filter
        if ($request->filled('sub_category_id')) {
            $query->where('sub_category_id', $request->sub_category_id);
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $microCategories = $query->latest()->paginate(10)->withQueryString();
        $subCategories = SubCategory::active()->with('category')->get();

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
        $data = $request->validate([
            'sub_category_id' => 'required|exists:sub_categories,id',
            'name'            => 'required|string|max:255',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active'       => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            // Auto Code Generation
            $subCat = SubCategory::findOrFail($data['sub_category_id']);
            $prefix = strtoupper(substr($subCat->code, 0, 3)) . '-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 3));
            $data['code'] = $prefix . '-' . strtoupper(Str::random(4));

            // Assign Creator
            $data['created_by'] = Auth::id();
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            // Image Upload
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = 'uploads/micro_categories';
                $file->move(public_path($path), $filename);
                $data['image_path'] = $path . '/' . $filename;
            }
            MicroCategory::create($data);
            DB::commit();

            // Dynamic Redirect based on Role
            $routePrefix = Auth::user()->hasRole('admin') ? 'admin.' : 'panel.';
            $params = Auth::user()->hasRole('admin') ? [] : ['role' => 'instructor'];

            return redirect()->route($routePrefix . 'micro-categories.index', $params)
                ->with('success', 'Micro-Category created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MicroCat Store Error: ' . $e->getMessage());
            return back()->with('error', 'Error creating micro-category.')->withInput();
        }
    }

    // --- FIXED EDIT METHOD (Handles Admin vs Panel routes) ---
    public function edit($p1, $p2 = null)
    {
        // 1. Resolve ID (If p2 exists, p1 is role, p2 is ID)
        $id = $p2 ?? $p1;

        $microCategory = ($id instanceof MicroCategory) ? $id : MicroCategory::findOrFail($id);
        $subCategories = SubCategory::active()->with('category')->get();

        // 2. Authorization Check (Only Admin or Owner can edit)
        if (!Auth::user()->hasRole('admin') && $microCategory->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action. You can only edit micro-categories you created.');
        }

        return view('admin.micro_categories.edit', compact('microCategory', 'subCategories'));
    }

    // --- FIXED UPDATE METHOD ---
    public function update(Request $request, $p1, $p2 = null)
    {
        // 1. Resolve ID
        $id = $p2 ?? $p1;
        $microCategory = ($id instanceof MicroCategory) ? $id : MicroCategory::findOrFail($id);

        // 2. Authorization Check
        if (!Auth::user()->hasRole('admin') && $microCategory->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'sub_category_id' => 'required|exists:sub_categories,id',
            'name'            => 'required|string|max:255',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active'       => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            if ($request->hasFile('image')) {
                // Delete Old Image
                if ($microCategory->image_path && File::exists(public_path($microCategory->image_path))) {
                    File::delete(public_path($microCategory->image_path));
                }
                // Upload New
                $file = $request->file('image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = 'uploads/micro_categories';
                $file->move(public_path($path), $filename);
                $data['image_path'] = $path . '/' . $filename;
            }

            $microCategory->update($data);
            DB::commit();

            $routePrefix = Auth::user()->hasRole('admin') ? 'admin.' : 'panel.';
            $params = Auth::user()->hasRole('admin') ? [] : ['role' => 'instructor'];

            return redirect()->route($routePrefix . 'micro-categories.index', $params)
                ->with('success', 'Micro-Category updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MicroCat Update Error: ' . $e->getMessage());
            return back()->with('error', 'Update failed.');
        }
    }

    // --- FIXED DESTROY METHOD ---
    public function destroy($p1, $p2 = null)
    {
        // 1. Resolve ID
        $id = $p2 ?? $p1;
        $microCategory = ($id instanceof MicroCategory) ? $id : MicroCategory::findOrFail($id);

        // 2. Authorization Check
        if (!Auth::user()->hasRole('admin') && $microCategory->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            if ($microCategory->image_path && File::exists(public_path($microCategory->image_path))) {
                File::delete(public_path($microCategory->image_path));
            }

            $microCategory->delete();
            return back()->with('success', 'Deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'System error during deletion.');
        }
    }
}
