<?php

namespace App\Models;

use App\Filters\QueryFilter;
use App\Traits\SecureDeletes;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\SchemalessAttributes\SchemalessAttributesTrait;

/**
 * @property \Spatie\SchemalessAttributes\SchemalessAttributes $settings
 * @method static Builder withSettings()
 */
class PracticeSet extends Model
{
    /** @use HasFactory<\Database\Factories\PracticeSetFactory> */
    use HasFactory;
    use Sluggable;
    use SoftDeletes;
    use SecureDeletes;
    use SchemalessAttributesTrait;
    use LogsActivity;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'practice_sets';

    /**
     * The attributes that aren't mass assignable.
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * Get the attributes that should be cast (Laravel 11/12 Method Style).
     */
    protected function casts(): array
    {
        return [
            // 'settings' => 'array', // <-- IMPORTANT: Isko comment hi rehne dein, warna conflict hoga
            'allow_rewards'   => 'boolean',
            'auto_grading'    => 'boolean',
            'total_questions' => 'integer',
            'is_active'       => 'boolean',
            'is_paid'         => 'boolean',
            'created_at'      => 'datetime',
            'updated_at'      => 'datetime',
        ];
    }

    /**
     * Define schemaless attributes column.
     */
    protected array $schemalessAttributes = [
        'settings',
    ];

    /**
     * Sluggable configuration.
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (PracticeSet $practiceSet) {
            if (empty($practiceSet->code)) {
                $practiceSet->code = 'set_' . Str::lower(Str::random(11));
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & MUTATORS (Modern Syntax)
    |--------------------------------------------------------------------------
    */

    /**
     * Handle Price conversion if applicable (Assuming storage in cents/paise).
     */
    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'practice_set_questions', 'practice_set_id', 'question_id')
            ->withTimestamps();
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(PracticeSession::class);
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

    public function scopeWithSettings(): Builder
    {
        // FIX: modelCast() -> modelScope()
        return $this->settings->modelScope();
    }

    public function scopePublished(Builder $query): void
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
            ->logOnly(['title', 'is_active', 'is_paid', 'total_questions', 'settings'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Practice Set has been {$eventName}");
    }
}
