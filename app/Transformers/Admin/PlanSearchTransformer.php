<?php

namespace App\Transformers\Admin;

use App\Models\Plan;
use League\Fractal\TransformerAbstract;

class PlanSearchTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer for search results.
     *
     * @param Plan $plan
     * @return array<string, mixed>
     */
    public function transform(Plan $plan): array
    {
        return [
            'id'   => $plan->id,
            'name' => $plan->full_name, // Assumes full_name is an attribute/accessor in your Plan model
        ];
    }
}