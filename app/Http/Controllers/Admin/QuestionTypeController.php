<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionType;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionTypeController extends Controller
{
    /**
     * List all Question Types.
     */
    public function index(Request $request): View|string
    {
        $query = QuestionType::query()->latest();

        // 1. Search Logic
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                  ->orWhere('code', 'like', '%' . $term . '%');
            });
        }

        // 2. Status Filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $types = $query->paginate(10)->withQueryString();

        // AJAX Response for Table Refresh
        if ($request->ajax()) {
            return view('admin.question_types.partials.table', compact('types'))->render();
        }

        return view('admin.question_types.index', compact('types'));
    }
}
