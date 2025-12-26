<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SyllabusController extends Controller
{
    // Search aur View dono isi function me handle karenge
    public function changeSyllabus(Request $request)
    {
        try {
            $query = SubCategory::active()
                ->has('sections')
                ->with([
                    'subCategoryType:id,name', // Only needed fields
                    'category:id,name'
                ]);

            // 1. Search Logic
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhereHas('category', function($cq) use ($search) {
                          $cq->where('name', 'like', '%' . $search . '%');
                      });
                });
            }

            $categories = $query->orderBy('name')->get();

            // 2. AJAX Check: Agar AJAX hai to sirf Cards ka HTML return karo
            if ($request->ajax()) {
                return view('student.change_syllabus.partials.card_list', compact('categories'))->render();
            }

            // Normal Request: Full Page return karo
            return view('student.change_syllabus.index', compact('categories'));

        } catch (Exception $e) {
            Log::error("Syllabus Page Error: " . $e->getMessage());

            if ($request->ajax()) {
                return '<div class="text-center text-red-500">Error loading data.</div>';
            }

            return view('student.change_syllabus.index', ['categories' => collect([])])
                ->with('error', 'Unable to load syllabus at the moment.');
        }
    }

    // ... updateSyllabus function same rahega ...
    public function updateSyllabus(Request $request): RedirectResponse
    {
        // ... (Apka purana code same rakhein) ...
        $request->validate([
            'category' => 'required|exists:sub_categories,code'
        ]);

        return DB::transaction(function () use ($request) {
            try {
                $category = SubCategory::where('code', $request->category)->firstOrFail();
                Cookie::queue('category_id', $category->id, 10080);
                Cookie::queue('category_name', $category->name, 10080);
                return redirect()->route('student.dashboard')->with('success', "Updated to {$category->name}");
            } catch (Exception $e) {
                DB::rollBack();
                Log::error("Syllabus Update Error: " . $e->getMessage());
                return redirect()->back()->with('error', 'Error updating preference.');
            }
        });
    }
}
