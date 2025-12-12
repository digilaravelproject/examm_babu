<?php
// app/Http/Controllers/Admin/QuizController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
// use App\Models\Category;    // optional: for filter selects
// use App\Models\QuizType;    // optional: for filter selects

class QuizController extends Controller
{
    public function index_old(Request $request)
    {
        $query = Quiz::query()->with(['category', 'quizType']);

        // apply filters if provided
        if ($request->filled('code')) {
            $query->where('code', 'like', '%'.$request->input('code').'%');
        }
        if ($request->filled('title')) {
            $query->where('title', 'like', '%'.$request->input('title').'%');
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }
        if ($request->filled('type')) {
            $query->where('type_id', $request->input('type'));
        }
        if ($request->filled('visibility')) {
            $query->where('visibility', $request->input('visibility'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // sort & paginate (10 per page default like screenshot)
        $quizzes = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        // for filter dropdowns (optional)
        $categories = Category::orderBy('name')->get();
        $types = QuizType::orderBy('name')->get();

        return view('admin.quizzes.index', compact('quizzes', 'categories', 'types'));
    }

    public function index(Request $request)
    {
        $data['quizzes'] = [];
        return view('admin.quizzes.index', $data);
    }

    // placeholder create method link (you can implement form in future)
    public function create()
    {
        return view('admin.quizzes.create'); // optional; create this later
    }
}
