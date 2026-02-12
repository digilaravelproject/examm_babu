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

class QuizType extends Model
{
    /** @use HasFactory<\Database\Factories\QuizTypeFactory> */
    use HasFactory;
    use Sluggable;
    use SoftDeletes;
    use SecureDeletes;
    use LogsActivity;

    /**
     * The attributes that aren't mass assignable.
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The accessors to append to the model's array form.
     * @var list<string>
     */
    protected $appends = ['plural_name'];

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
        static::creating(function (QuizType $quizType) {
            if (empty($quizType->code)) {
                $quizType->code = 'qtp_' . Str::lower(Str::random(11));
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (Modern Laravel 12 Syntax)
    |--------------------------------------------------------------------------
    */

    /**
     * Get the plural form of the Quiz Type name.
     */
    protected function pluralName(): Attribute
    {
        return Attribute::make(
            get: fn () => Str::plural($this->name),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Relationship with Quizzes.
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    /**
     * Get the latest quiz of this type.
     */
    public function latestQuiz(): HasOne
    {
        // 'latestOfMany' is a more performant way in modern Laravel
        return $this->hasOne(Quiz::class)->latestOfMany();
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Apply custom query filters.
     */
    public function scopeFilter(Builder $query, QueryFilter $filters): Builder
    {
        return $filters->apply($query);
    }

    /**
     * Scope to only include active quiz types.
     */
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
            ->setDescriptionForEvent(fn(string $eventName) => "Quiz Type has been {$eventName}");
    }
}
