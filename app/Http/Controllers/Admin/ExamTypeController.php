<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ExamTypeController extends Controller
{
    /**
     * Helper: Determine Route Prefix (admin. or panel.)
     */
    private function getRoutePrefix(): string
    {
        return Auth::user()->hasRole('admin') ? 'admin.' : 'panel.';
    }

    /**
     * Helper: Get Route Params (Fix for Missing Parameter: role)
     */
    private function getRouteParams(): array
    {
        if (Auth::user()->hasRole('admin')) {
            return [];
        }
        return ['role' => request()->route('role') ?? 'instructor'];
    }

    public function index(Request $request)
    {
        $query = ExamType::query();

        // Search Logic
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        // Status Logic
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $examTypes = $query->latest()->paginate(10);

        // --- AJAX CHECK ---
        if ($request->ajax()) {
            return view('admin.exam-types._table', compact('examTypes'))->render();
        }

        return view('admin.exam-types.index', compact('examTypes'));
    }

    public function create()
    {
        return view('admin.exam-types.create');
    }

    public function store(Request $request)
    {
        // 1. Validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:exam_types,code',
            'color' => 'nullable|string|max:50',
            'image_path' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        // 2. Active Status Handling
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        // 3. Auto Generate Code if empty
        if (empty($validated['code'])) {
            $validated['code'] = strtoupper(\Illuminate\Support\Str::slug($validated['name']));
        }

        // 4. Create
        ExamType::create($validated);

        // FIX: Dynamic Redirect with Params
        return redirect()->route($this->getRoutePrefix() . 'exam-types.index', $this->getRouteParams())
                         ->with('success', 'Exam Type created successfully!');
    }

    // FIX: Use Request to get ID safely
    public function edit(Request $request)
    {
        // 'exam_type' is the standard resource parameter name
        $id = $request->route('exam_type');
        $examType = ExamType::findOrFail($id);

        return view('admin.exam-types.edit', compact('examType'));
    }

    // FIX: Use Request to get ID safely
    public function update(Request $request)
    {
        $id = $request->route('exam_type');
        $examType = ExamType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // Unique check ignore current ID
            'code' => ['required', 'string', 'max:50', Rule::unique('exam_types')->ignore($examType->id)],
            // Handle boolean check properly
            'is_active' => 'sometimes',
        ]);

        // Checkbox handling for update
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $examType->update($validated);

        // FIX: Dynamic Redirect with Params
        return redirect()->route($this->getRoutePrefix() . 'exam-types.index', $this->getRouteParams())
            ->with('success', 'Exam Type updated successfully!');
    }

    // FIX: Use Request to get ID safely
    public function destroy(Request $request)
    {
        $id = $request->route('exam_type');
        $examType = ExamType::findOrFail($id);

        // Optional: Check relationships before delete
        if($examType->exams()->exists()) {
             return redirect()->back()->with('error', 'Cannot delete: This Exam Type is assigned to existing Exams.');
        }

        $examType->delete();

        // FIX: Dynamic Redirect with Params
        return redirect()->route($this->getRoutePrefix() . 'exam-types.index', $this->getRouteParams())
            ->with('success', 'Exam Type deleted successfully!');
    }
}