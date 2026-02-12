<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeFeature extends Model
{
    use HasFactory;

    protected $table = 'home_features';

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'title',
        'description',
        'icon',
        'bg_class',
        'sort_order',
        'is_active',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'is_active'   => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Default sorting
     */
    protected static function booted()
    {
        static::addGlobalScope('sortOrder', function ($query) {
            $query->orderBy('sort_order');
        });
    }

    /**
     * Scope: active features only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
