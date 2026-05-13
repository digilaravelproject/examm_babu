<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeStat extends Model
{
    use HasFactory;

    protected $table = 'home_stats';

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'count',
        'label',
        'icon',
        'text_class',
        'bg_class',
        'sort_order',
        'is_active',
    ];

    /**
     * Cast attributes to native types
     */
    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Default sorting by sort_order
     */
    protected static function booted()
    {
        static::addGlobalScope('sortOrder', function ($query) {
            $query->orderBy('sort_order');
        });
    }

    /**
     * Scope: only active stats
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
