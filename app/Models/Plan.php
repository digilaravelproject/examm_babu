<?php

namespace App\Models;

use App\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Plan $plan) {
            if (empty($plan->code)) {
                $plan->code = 'plan_' . Str::lower(Str::random(11));
            }
        });
    }

    public function category(): BelongsTo
    {
        // Database column: category_id
        return $this->belongsTo(SubCategory::class, 'category_id');
    }

    public function features(): BelongsToMany
    {
        // Removed withTimestamps() to avoid SQL error
        return $this->belongsToMany(Feature::class, 'plan_features', 'plan_id', 'feature_id');
    }

    public function scopeFilter(Builder $query, QueryFilter $filters): Builder
    {
        return $filters->apply($query);
    }
}
