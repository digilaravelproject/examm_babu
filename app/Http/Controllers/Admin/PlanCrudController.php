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
    public function index(Request $request, PlanFilters $filters)
    {
        $plansCollection = Plan::with('category:id,name')
            ->filter($filters)
            ->latest()
            ->paginate(request('perPage', 10));

        $plansData = fractal($plansCollection, new PlanTransformer())->toArray();

        if ($request->ajax()) {
            return view('admin.plans.partials.table', [
                'plans' => $plansData,
                'paginator' => $plansCollection
            ])->render();
        }

        return view('admin.plans.index', [
            'plans'         => $plansData,
            'paginator'     => $plansCollection,
            'features'      => Feature::select(['id', 'name'])->active()->get(),
            'subCategories' => SubCategory::select(['id', 'name'])->active()->get()
        ]);
    }

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

        // 2. Features extract karein
        $features = $data['features'] ?? [];
        unset($data['features']);

        // 3. Default Category Type
        $data['category_type'] = \App\Models\SubCategory::class;

        // 4. Create Plan
        $plan = Plan::create($data);

        // 5. Sync Features only if Restrictions are ENABLED
        if (!empty($features) && $data['feature_restrictions'] == 1) {
            $plan->features()->sync($features);
        }

        return redirect()->route('admin.plans.index')->with('success', 'Plan created successfully!');
    }

    public function show(Plan $plan): array
    {
        return fractal($plan, new PlanTransformer())->toArray();
    }

    public function edit(Plan $plan)
    {
        $subCategories = \App\Models\SubCategory::select(['id', 'name'])->get();
        $features = \App\Models\Feature::select(['id', 'name'])->active()->get();
        $selectedFeatures = $plan->features()->pluck('features.id')->toArray();

        return view('admin.plans.edit', compact('plan', 'subCategories', 'features', 'selectedFeatures'));
    }

    /**
     * Update a plan
     */
     public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        if (config('qwiktest.demo_mode')) {
            return back()->with('error', "Demo Mode! Plans can't be changed.");
        }

        // 1. Data Get (Validated & Cleaned by Request class)
        $data = $request->validated();

        // --- FIX START ---
        // Extract features to a variable and remove it from the $data array
        $features = $data['features'] ?? []; 
        unset($data['features']); 
        // --- FIX END ---

        // 2. Update Plan (Now $data only contains columns that actually exist in the DB)
        $plan->update($data);

        // 3. Sync Features
        if (isset($data['feature_restrictions']) && $data['feature_restrictions'] == 1) {
             $plan->features()->sync($features);
        } else {
             $plan->features()->detach(); 
        }

        return redirect()->route('admin.plans.index')->with('success', 'Plan was successfully updated!');
    }
    public function update_old(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        if (config('qwiktest.demo_mode')) {
            return back()->with('error', "Demo Mode! Plans can't be changed.");
        }

        // 1. Data Get (Validated & Cleaned by Request class)
        $data = $request->validated();

        // 2. Update Plan
        $plan->update($data);
        if ($data['feature_restrictions'] == 1) {
             $featureIds = $request->features ?? [];
             $plan->features()->sync($featureIds);
        } else {
             $plan->features()->detach(); 
        }

        return redirect()->route('admin.plans.index')->with('success', 'Plan was successfully updated!');
    }

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
                $plan->features()->detach();
                $plan->forceDelete();
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