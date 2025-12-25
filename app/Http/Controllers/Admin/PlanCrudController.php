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
     * List all plans (Using Blade View)
     */
    public function index(PlanFilters $filters): View
    {
        // We still use fractal to transform the data for the view
        $plansCollection = Plan::with('category:id,name')
            ->filter($filters)
            ->paginate(request('perPage', 10));

        $plans = fractal($plansCollection, new PlanTransformer())->toArray();

        return view('admin.plans.index', [
            'plans'         => $plans, // This will contain 'data' and 'meta' (pagination)
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
    public function store(StorePlanRequest $request): RedirectResponse
    {
        $plan = Plan::create($request->validated());

        $featureIds = $plan->feature_restrictions 
            ? $request->features 
            : Feature::active()->pluck('id');

        $plan->features()->sync($featureIds);

        return back()->with('successMessage', 'Plan was successfully added!');
    }

    /**
     * Show a plan (Standard Array for API/AJAX)
     */
    public function show(Plan $plan): array
    {
        return fractal($plan, new PlanTransformer())->toArray();
    }

    /**
     * Edit a plan (Return JSON for Modal population)
     */
    public function edit(Plan $plan): JsonResponse
    {
        return response()->json([
            'plan' => $plan,
            'features' => $plan->features()->pluck('id'),
        ]);
    }

    /**
     * Update a plan
     */
    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        if (config('qwiktest.demo_mode')) {
            return back()->with('errorMessage', "Demo Mode! Plans can't be changed.");
        }

        $plan->update($request->validated());

        $featureIds = $plan->feature_restrictions 
            ? $request->features 
            : Feature::active()->pluck('id');

        $plan->features()->sync($featureIds);

        return back()->with('successMessage', 'Plan was successfully updated!');
    }

    /**
     * Delete a plan
     */
    public function destroy(Plan $plan): RedirectResponse
    {
        if (config('qwiktest.demo_mode')) {
            return back()->with('errorMessage', "Demo Mode! Plans can't be deleted.");
        }

        try {
            DB::transaction(function () use ($plan) {
                $plan->subscriptions()->forceDelete();
                $plan->payments()->forceDelete();
                $plan->secureDelete('subscriptions', 'payments');
            });
        } catch (\Exception $e) {
            return back()->with('errorMessage', 'Unable to Delete Plan. Remove all associations and try again!');
        }

        return back()->with('successMessage', 'Plan was successfully deleted!');
    }
}