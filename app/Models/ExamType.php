<?php

namespace App\Models;

use App\Filters\QueryFilter;
use App\Traits\SecureDeletes;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ExamType extends Model
{
    use HasFactory, Sluggable, SoftDeletes, SecureDeletes, LogsActivity;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    // ✅ FIXED: Using 'image_path' instead of 'image_url'
    protected $fillable = [
        'name',
        'code',
        'slug',
        'description',
        'image_path',
        'color',
        'is_active'
    ];

    protected $appends = ['plural_name'];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ExamType $examType) {
            if (empty($examType->code)) {
                $examType->code = 'ETP_' . Str::upper(Str::random(8));
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS & SCOPES
    |--------------------------------------------------------------------------
    */

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    protected function pluralName(): Attribute
    {
        return Attribute::make(
            get: fn() => Str::plural($this->name)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG
    |--------------------------------------------------------------------------
    */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'slug', 'is_active', 'color', 'image_path', 'description']) // ✅ Fixed here too
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Exam Type has been {$eventName}");
    }
}
