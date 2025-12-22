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
        // Adjust middleware permission name based on your Spatie config
        // $this->middleware(['permission:view-comprehensions'])->only(['index', 'show']);
        // $this->middleware(['permission:create-comprehensions'])->only(['create', 'store']);
        // $this->middleware(['permission:edit-comprehensions'])->only(['edit', 'update']);
        // $this->middleware(['permission:delete-comprehensions'])->only(['destroy']);
    }

    /**
     * List all passages with Filters (Search, Status).
     */
    public function index(Request $request): View|string
    {
        $query = ComprehensionPassage::query()->latest();

        // 1. Search Filter
        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = $request->search;
            $q->where(function ($sub) use ($term) {
                $sub->where('title', 'like', '%' . $term . '%')
                    ->orWhere('code', 'like', '%' . $term . '%');
            });
        });

        // 2. Status Filter
        $query->when($request->filled('status'), function ($q) use ($request) {
            if ($request->status === 'active') {
                $q->where('is_active', true);
            } elseif ($request->status === 'pending') { // Assuming pending means inactive
                $q->where('is_active', false);
            }
        });

        // Pagination
        $passages = $query->paginate(10)->withQueryString();

        // AJAX Response for Table Refresh
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
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only(['title', 'body', 'is_active']);

            // Auto-generate code if model doesn't handle it
            $data['code'] = 'cmp_' . Str::lower(Str::random(10));

            ComprehensionPassage::create($data);

            DB::commit();
            return redirect()->route('admin.comprehensions.index')
                             ->with('success', 'Comprehension passage created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Comprehension Store Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create passage.')->withInput();
        }
    }

    /**
     * Show Edit Form.
     */
    public function edit($id): View
    {
        $passage = ComprehensionPassage::findOrFail($id);
        return view('admin.comprehensions.edit', compact('passage'));
    }

    /**
     * Update Passage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'title'     => 'required|string|max:255',
            'body'      => 'required|string',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $passage = ComprehensionPassage::findOrFail($id);

            $passage->update($request->only(['title', 'body', 'is_active']));

            DB::commit();
            return redirect()->route('admin.comprehensions.index')
                             ->with('success', 'Comprehension passage updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Comprehension Update Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update passage.');
        }
    }

    /**
     * Delete Passage (Safely detach questions first).
     */
    public function destroy($id): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $passage = ComprehensionPassage::findOrFail($id);

            // Detach this passage from all linked questions first
            // This prevents foreign key constraint errors or orphaned data
            Question::where('comprehension_passage_id', $passage->id)
                ->update([
                    'has_attachment' => false,
                    'attachment_type' => null,
                    'comprehension_passage_id' => null
                ]);

            $passage->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Passage deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Comprehension Delete Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error deleting passage: ' . $e->getMessage());
        }
    }
}
