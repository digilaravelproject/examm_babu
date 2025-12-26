<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Settings\CategorySettings;

class StudentDashboardController extends Controller
{
    /**
     * User's Main Dashboard
     */
    public function index()
    {
        $user = Auth::user();
        return view('student.dashboard', compact('user'));
    }

    /**
     * Add Exams page - Shows all categories
     * * @param CategorySettings $categorySettings
     * @return \Illuminate\Contracts\View\View
     */
    public function addExams(CategorySettings $categorySettings)
    {
        // Load only parent categories (unique), but eager-load active subcategories
        $categories = Category::with(['subCategories' => function ($query) {
            $query->where('is_active', true);
        }])
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->limit($categorySettings->limit ?? 50)
            ->get();

        // Note: Maine view path 'user.add_exams' se change karke 'student.add_exams' kar diya hai
        // taaki yeh aapke new folder structure ke hisaab se rahe.
        return view('student.add_exams', [
            'category' => $categorySettings->toArray(),
            'categories' => $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'short_description' => $category->short_description,
                ];
            }),
        ]);
    }
}
