<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MicroCategory extends Model
{
    use HasFactory;

    // Table Name (Optional, but good for clarity)
    protected $table = 'micro_categories';

    // Mass Assignment Protection
    // Sirf ye columns hi create/update ho sakte hain
    protected $fillable = [
        'sub_category_id',
        'name',
        'code',
        'image_path',
        'is_active',
        'created_by',
    ];

    /**
     * Relationship: Micro Category belongs to a Sub Category
     */
    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: MicroCategory has many Plans
     * Plans link to MicroCategory via category_id
     */
    public function plans()
    {
        return $this->hasMany(Plan::class, 'category_id');
    }

    /**
     * Relationship: MicroCategory has many Skills (Subjects in UI)
     */
    public function skills()
    {
        return $this->hasMany(Skill::class);
    }
    /**
     * Scope: Sirf Active records fetch karne ke liye
     * Use: MicroCategory::active()->get();
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
