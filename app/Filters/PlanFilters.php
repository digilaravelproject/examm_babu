<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class PlanFilters extends QueryFilter
{
    public function name(string $query = ""): Builder
    {
        return $this->builder->where('name', 'like', "%{$query}%");
    }

    public function category_id($id = null): Builder
    {
        return $this->builder->where('category_id', $id);
    }

    public function status($status = null): Builder
    {
        if ($status === null || $status === '') return $this->builder;
        return $this->builder->where('is_active', (bool)$status);
    }
}
