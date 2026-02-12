<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
use Illuminate\Http\Request;

class ExamTypeController extends Controller
{
    // List Page
    public function index(Request $request)
    {
        $query = ExamType::query();

        // Search Filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }

        // Pagination
        $examTypes = $query->latest()->paginate(10);

        return view('admin.exam-types.index', compact('examTypes'));
    }

    // Create Page
    public function create()
    {
        return view('admin.exam-types.create');
    }

    // Store Logic
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:exam_types,code',
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        ExamType::create($request->all());

        return redirect()->route('exam-types.index')
            ->with('success', 'Exam Type created successfully!');
    }

    // Edit Page
    public function edit($id)
    {
        $examType = ExamType::findOrFail($id);
        return view('admin.exam-types.edit', compact('examType'));
    }

    // Update Logic
    public function update(Request $request, $id)
    {
        $examType = ExamType::findOrFail($id);

        $request->validate([
            'code' => 'required|unique:exam_types,code,' . $examType->id,
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        $examType->update($request->all());

        return redirect()->route('exam-types.index')
            ->with('success', 'Exam Type updated successfully!');
    }

    // Delete Logic
    public function destroy($id)
    {
        $examType = ExamType::findOrFail($id);
        $examType->delete();

        return redirect()->route('exam-types.index')
            ->with('success', 'Exam Type deleted successfully!');
    }
}
