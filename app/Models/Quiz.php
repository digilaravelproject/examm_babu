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
class Quiz extends Model
{
    /** @use HasFactory<\Database\Factories\QuizFactory> */
    use HasFactory;
    use Sluggable;
    use SchemalessAttributesTrait;
    use SoftDeletes;
    use SecureDeletes;
    use LogsActivity;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'quizzes';

    /**
     * The attributes that aren't mass assignable.
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_paid' => 'boolean',
            'is_private' => 'boolean',
            'is_active' => 'boolean',
            'can_redeem' => 'boolean',
            'total_marks' => 'decimal:2',
            'total_duration' => 'integer',
            'total_questions' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
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
        static::creating(function (Quiz $quiz) {
            if (empty($quiz->code)) {
                $quiz->code = 'quiz_' . Str::lower(Str::random(11));
            }
        });
    }

    /**
     * Sync metadata (marks, duration, question count)
     */
    public function updateMeta(): void
    {
        $questions = $this->questions();
        $this->total_questions = $questions->count();

        // Calculate Duration
        if ($this->settings->get('auto_duration', true)) {
            $this->total_duration = $questions->sum('default_time');
        }

        // Calculate Marks
        if ($this->settings->get('auto_grading', true)) {
            $this->total_marks = $questions->sum('default_marks');
        } else {
            $correctMarks = $this->settings->get('correct_marks', 1);
            $this->total_marks = $this->total_questions * $correctMarks;
        }

        // Use saveQuietly to prevent infinite observer loops
        $this->saveQuietly();
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & MUTATORS (Modern Laravel 12 Syntax)
    |--------------------------------------------------------------------------
    */

    /**
     * Handle Price conversion (Stored in cents/paise in DB)
     */
    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn (int|float $value) => $value / 100,
            set: fn (int|float $value) => $value * 100,
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

    public function quizType(): BelongsTo
    {
        return $this->belongsTo(QuizType::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'quiz_questions', 'quiz_id', 'question_id')
            ->withTimestamps();
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(QuizSession::class);
    }

    public function quizSchedules(): HasMany
    {
        return $this->hasMany(QuizSchedule::class);
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
        return $this->settings->modelScope();
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeIsPublic(Builder $query): void
    {
        $query->where('is_private', false);
    }

    public function scopeIsPrivate(Builder $query): void
    {
        $query->where('is_private', true);
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG CONFIG
    |--------------------------------------------------------------------------
    */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'is_active', 'is_paid', 'total_marks', 'total_questions'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Quiz has been {$eventName}");
    }
}
