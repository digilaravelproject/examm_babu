<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    protected $fillable = [
        'title',
        'image_path',
        'link_url',
        'status',
        'location',
    ];

    /**
     * Scope a query to only include active advertisements.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
