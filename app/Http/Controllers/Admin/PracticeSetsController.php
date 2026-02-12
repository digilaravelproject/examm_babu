<?php
// app/Http/Controllers/Admin/QuizController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PracticeSets;
// use App\Models\Category;    // optional: for filter selects
// use App\Models\QuizType;    // optional: for filter selects

class PracticeSetsController extends Controller
{
    public function index(Request $request)
    {
        $data['quizzes'] = [];
        return view('admin.practice-sets.index', $data);
    }

    // placeholder create method link (you can implement form in future)
    public function create()
    {
        return view('admin.practice-sets.create'); // optional; create this later
    }
}
