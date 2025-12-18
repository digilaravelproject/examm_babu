<?php

namespace App\Models;

use App\Filters\QueryFilter;
use App\Traits\SecureDeletes;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Section extends Model
{
    /** @use HasFactory<\Database\Factories\SectionFactory> */
    use HasFactory;
    use Sluggable;
    use SoftDeletes;
    use SecureDeletes;
    use LogsActivity;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'sections';

    /**
     * The attributes that aren't mass assignable.
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Sluggable configuration.
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Section $section) {
            if (empty($section->code)) {
                $section->code = 'sec_' . Str::lower(Str::random(11));
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */



    /**
     * Direct relationship with Skills.
     */
    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }

    /**
     * Get all topics through Skills.
     */
    public function topics(): HasManyThrough
    {
        return $this->hasManyThrough(Topic::class, Skill::class);
    }

    /**
     * Get all questions through Skills.
     */
    public function questions(): HasManyThrough
    {
        return $this->hasManyThrough(Question::class, Skill::class);
    }

    /**
     * Get all practice sets through Skills.
     */
    public function practiceSets(): HasManyThrough
    {
        return $this->hasManyThrough(PracticeSet::class, Skill::class);
    }

    /**
     * SubCategories that this section belongs to.
     */
    public function subCategories(): BelongsToMany
    {
        return $this->belongsToMany(SubCategory::class, 'sub_category_sections', 'section_id', 'sub_category_id')
            ->withTimestamps();
    }

    /**
     * Relationship with Exam Sections.
     */
    public function examSections(): HasMany
    {
        return $this->hasMany(ExamSection::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeFilter(Builder $query, QueryFilter $filters): Builder
    {
        return $filters->apply($query);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG CONFIG
    |--------------------------------------------------------------------------
    */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'is_active', 'code'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Section has been {$eventName}");
    }
}
