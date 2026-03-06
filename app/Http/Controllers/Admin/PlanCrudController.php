<?php

namespace App\Http\Controllers\Admin;

use App\Filters\PlanFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Models\Feature;
use App\Models\MicroCategory;
use App\Models\Plan;
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
        $plansCollection = Plan::with('microCategory:id,name')
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
            'features'        => Feature::select(['id', 'name'])->active()->get(),
            'microCategories' => MicroCategory::select(['id', 'name'])->active()->get()
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
        $data['category_type'] = MicroCategory::class;
        Plan::create($data);

        return redirect()->route('admin.plans.index')->with('success', 'Plan created successfully!');
    }

    public function show(Plan $plan): array
    {
        return fractal($plan, new PlanTransformer())->toArray();
    }

    public function edit(Plan $plan)
    {
        $microCategories = MicroCategory::select(['id', 'name'])->active()->get();
        $features = Feature::select(['id', 'name'])->active()->get();
        $selectedFeatures = $plan->features()->pluck('features.id')->toArray();

        return view('admin.plans.edit', compact('plan', 'microCategories', 'features', 'selectedFeatures'));
    }

    /**
     * Update a plan
     */
     public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $data = $request->validated();
        $plan->update($data);
        return redirect()->route('admin.plans.index')->with('success', 'Plan was successfully updated!');
    }

    public function destroy(Plan $plan)
    {

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
