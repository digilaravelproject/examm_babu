<?php

namespace App\Http\Controllers\Admin;

use App\Filters\PlanFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\SubCategory;
use App\Transformers\Admin\PlanSearchTransformer;
use App\Transformers\Admin\PlanTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PlanCrudController extends Controller
{
    /**
     * List all plans with AJAX support.
     */
    public function index(Request $request, PlanFilters $filters)
    {
        // 1. Base Query with Filters
        $plansCollection = Plan::with('category:id,name')
            ->filter($filters)
            ->latest()
            ->paginate(request('perPage', 10));

        // 2. Transform Data (Fractal)
        $plansData = fractal($plansCollection, new PlanTransformer())->toArray();

        // 3. Handle AJAX Request (Search/Filter/Pagination)
        if ($request->ajax()) {
            return view('admin.plans.partials.table', [
                'plans' => $plansData, // Data array for loop
                'paginator' => $plansCollection // Raw Paginator object for links()
            ])->render();
        }

        // 4. Standard Page Load
        return view('admin.plans.index', [
            'plans'         => $plansData,
            'paginator'     => $plansCollection,
            'features'      => Feature::select(['id', 'name'])->active()->get(),
            'subCategories' => SubCategory::select(['id', 'name'])->active()->get()
        ]);
    }

    /**
     * Search plans api endpoint
     */
    public function search(Request $request, PlanFilters $filters): JsonResponse
    {
        $query = $request->get('query');

        $plans = Plan::filter($filters)
            ->where('name', 'like', "%{$query}%")
            ->limit(20)
            ->get();

        return response()->json([
            'plans' => fractal($plans, new PlanSearchTransformer())->toArray()['data']
        ]);
    }

    /**
     * Store a plan
     */
    public function store(StorePlanRequest $request)
    {
        $data = $request->validated();

        // Checkbox handling
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        // Set Category Type (Polymorphic)
        $data['category_type'] = SubCategory::class;

        Plan::create($data);

        return redirect()->route('admin.plans.index')->with('success', 'Plan created successfully!');
    }

    /**
     * Show a plan (Standard Array for API/AJAX)
     */
    public function show(Plan $plan): array
    {
        return fractal($plan, new PlanTransformer())->toArray();
    }

    /**
     * Edit a plan (Return View)
     */
    public function edit(Plan $plan)
    {
        $subCategories = SubCategory::select(['id', 'name'])->get();
        return view('admin.plans.edit', compact('plan', 'subCategories'));
    }

    /**
     * Update a plan
     */
    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        if (config('qwiktest.demo_mode')) {
            return back()->with('error', "Demo Mode! Plans can't be changed.");
        }

        $data = $request->validated();

        // Handle Checkbox for Update
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        $plan->update($data);

        // Feature Sync Logic
        $featureIds = $plan->feature_restrictions
            ? $request->features
            : Feature::active()->pluck('id');

        $plan->features()->sync($featureIds);

        // ✅ REDIRECT TO INDEX (Not Back)
        return redirect()->route('admin.plans.index')->with('success', 'Plan was successfully updated!');
    }

    /**
     * Delete a plan
     */
    public function destroy(Plan $plan)
    {
        if (config('qwiktest.demo_mode')) {
            return response()->json([
                'success' => false,
                'message' => "Demo Mode! Plans can't be deleted."
            ], 403);
        }

        try {
            DB::transaction(function () use ($plan) {
                // Detach Features
                $plan->features()->detach();

                // Delete Relations (Optional)
                if (method_exists($plan, 'subscriptions')) {
                    // $plan->subscriptions()->delete();
                }

                // Delete Plan
                $plan->delete();
            });

            return response()->json([
                'success' => true,
                'message' => "Plan '{$plan->name}' deleted successfully."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to Delete: ' . $e->getMessage()
            ], 500);
        }
    }
}
