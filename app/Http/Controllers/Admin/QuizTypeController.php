<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// If you have a model, it will be used. Otherwise controller provides demo data.
use App\Models\QuizType; // optional - only if you have this model

class QuizTypeController extends Controller
{
    public function index(Request $request)
    {
        $data['quizTypes'] = [];
        return view('admin.quiz-types.index', $data);
    }
}
