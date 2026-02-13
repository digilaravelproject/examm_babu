<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComprehensionPassage;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ComprehensionController extends Controller
{
    public function __construct()
    {
    }

    // Helper: Determine Route Prefix (admin. or panel.)
    private function getRoutePrefix(): string
    {
        return Auth::user()->hasRole('admin') ? 'admin.' : 'panel.';
    }

    // Helper: Authorize Instructor Ownership
    private function authorizeInstructor(ComprehensionPassage $passage): void
    {
        if (Auth::user()->hasRole('instructor') && $passage->created_by != Auth::id()) {
            abort(403, 'You are not authorized to modify this passage.');
        }
    }

    /**
     * List all passages with Filters.
     */
    public function index(Request $request): View|string
    {
        $query = ComprehensionPassage::query()->with('creator')->latest();

        // 1. Instructor Filter: Only see own passages
        if (Auth::user()->hasRole('instructor')) {
            $query->where('created_by', Auth::id());
        }

        // 2. Search Filter
        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = $request->search;
            $q->where(function ($sub) use ($term) {
                $sub->where('title', 'like', '%' . $term . '%')
                    ->orWhere('code', 'like', '%' . $term . '%');
            });
        });

        // 3. Status Filter
        $query->when($request->filled('status'), function ($q) use ($request) {
            if ($request->status === 'active') {
                $q->where('is_active', true);
            } elseif ($request->status === 'pending') {
                $q->where('is_active', false);
            }
        });

        // Eager load Question Count
        $query->withCount('questions');

        $passages = $query->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('admin.comprehensions.partials.table', compact('passages'))->render();
        }

        return view('admin.comprehensions.index', compact('passages'));
    }

    /**
     * Show Create Form.
     */
    public function create(): View
    {
        return view('admin.comprehensions.create');
    }

    /**
     * Store new Passage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title'     => 'required|string|max:255',
            'body'      => 'required|string',
            'is_active' => 'boolean', // Ensures it's 0 or 1
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only(['title', 'body']);

            // Handle Checkbox (If unchecked, it's not in request)
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            // Generate Code
            $data['code'] = 'cmp_' . Str::lower(Str::random(10));

            // Assign Creator
            $data['created_by'] = Auth::id();

            ComprehensionPassage::create($data);

            DB::commit();

            // Dynamic Redirect
            return redirect()->route($this->getRoutePrefix() . 'comprehensions.index')
                             ->with('success', 'Comprehension passage created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Comprehension Store Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create passage.')->withInput();
        }
    }

    /**
     * AJAX Store for Inline Creation.
     */
    public function storeAjax(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body'  => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only(['title', 'body']);
            $data['is_active'] = 1; // Default active for inline creation
            $data['code'] = 'cmp_' . Str::lower(Str::random(10));
            $data['created_by'] = Auth::id();

            $passage = ComprehensionPassage::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Passage created.',
                'passage' => [
                    'id' => $passage->id,
                    'title' => $passage->title
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Comprehension Ajax Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to create passage.'], 500);
        }
    }

    /**
     * Show Edit Form.
     * Use Request to manually fetch ID to avoid {role} conflict.
     */
    public function edit(Request $request): View
    {
        // Fetch ID explicitly from route param 'comprehension'
        $id = $request->route('comprehension');

        $passage = ComprehensionPassage::findOrFail($id);

        $this->authorizeInstructor($passage);

        return view('admin.comprehensions.edit', compact('passage'));
    }

    /**
     * Update Passage.
     */
    public function update(Request $request): RedirectResponse
    {
        $id = $request->route('comprehension');

        $request->validate([
            'title'     => 'required|string|max:255',
            'body'      => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $passage = ComprehensionPassage::findOrFail($id);
            $this->authorizeInstructor($passage);

            $data = $request->only(['title', 'body']);

            // Handle Checkbox explicitly
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            $passage->update($data);

            DB::commit();

            return redirect()->route($this->getRoutePrefix() . 'comprehensions.index')
                             ->with('success', 'Comprehension passage updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Comprehension Update Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update passage.');
        }
    }

    /**
     * Show Details (Optional, uses Edit view or separate show view).
     */
    public function show(Request $request): View
    {
        $id = $request->route('comprehension');
        $passage = ComprehensionPassage::findOrFail($id);
        $this->authorizeInstructor($passage);

        return view('admin.comprehensions.edit', compact('passage'));
    }

    /**
     * Show Usage Details.
     */
    public function usage(Request $request): View
    {
        $id = $request->route('comprehension');
        $passage = ComprehensionPassage::with(['questions.exams', 'questions.questionType'])->findOrFail($id);

        $this->authorizeInstructor($passage);

        return view('admin.comprehensions.usage', compact('passage'));
    }

    /**
     * Delete Passage.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $id = $request->route('comprehension');

        DB::beginTransaction();
        try {
            $passage = ComprehensionPassage::findOrFail($id);
            $this->authorizeInstructor($passage);

            // Detach from linked questions
            Question::where('comprehension_passage_id', $passage->id)
                ->update([
                    'has_attachment' => false,
                    'attachment_type' => null,
                    'comprehension_passage_id' => null
                ]);

            $passage->delete();

            DB::commit();

            return redirect()->route($this->getRoutePrefix() . 'comprehensions.index')
                             ->with('success', 'Passage deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Comprehension Delete Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error deleting passage: ' . $e->getMessage());
        }
    }
}
