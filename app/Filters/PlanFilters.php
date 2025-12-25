<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class PlanFilters extends QueryFilter
{
    /**
     * Filter by name using like operator
     */
    public function name(string $query = ""): Builder
    {
        return $this->builder->where('name', 'like', "%{$query}%");
    }

    /**
     * Filter by code using like operator
     */
    public function code(string $query = ""): Builder
    {
        return $this->builder->where('code', 'like', "%{$query}%");
    }

    /**
     * Filter by exact duration
     */
    public function duration(mixed $query = null): Builder
    {
        return $this->builder->where('duration', $query);
    }

    /**
     * Filter by status (is_active)
     */
    public function status(mixed $status = 0): Builder
    {
        // Using match for cleaner handling of boolean/integer statuses
        $isActive = match ($status) {
            'active', '1', 1, true => 1,
            default => 0,
        };

        return $this->builder->where('is_active', $isActive);
    }
}