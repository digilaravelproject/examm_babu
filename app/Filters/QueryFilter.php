<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class QueryFilter
{
    /**
     * The Request instance.
     */
    protected Request $request;

    /**
     * The Eloquent builder instance.
     */
    protected Builder $builder;

    /**
     * Create a new QueryFilter instance.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Apply the filters to the builder.
     *
     * @param Builder $builder
     * @return Builder
     */
    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        foreach ($this->filters() as $name => $value) {
            // Agar class mein method hai aur value null nahi hai
            if (method_exists($this, $name)) {
                // Check if value is not empty (allowing '0')
                if (strlen((string) $value) > 0) {
                    $this->$name($value);
                }
            }
        }

        return $this->builder;
    }

    /**
     * Get all request filters.
     *
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return $this->request->all();
    }
}
