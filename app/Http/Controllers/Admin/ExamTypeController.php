<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// If you have a model, it will be used. Otherwise controller provides demo data.
use App\Models\ExamType; // optional - only if you have this model

class ExamTypeController extends Controller
{
    public function index(Request $request)
    {
        $data['examTypes'] = [];
        return view('admin.exam-types.index', $data);
    }
}
