<?php

namespace App\Models;

use App\Filters\QueryFilter;
use App\Traits\SecureDeletes;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
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

class Exam extends Model
{
    /** @use HasFactory<\Database\Factories\ExamFactory> */
    use HasFactory;
    use SoftDeletes;
    use Sluggable;
    use SecureDeletes;
    use SchemalessAttributesTrait;
    use LogsActivity;

    // --- STATUS CONSTANTS ---
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_PUBLISHED = 'published';
    const STATUS_REJECTED = 'rejected';

    protected $table = 'exams';
    protected $guarded = []; // Allows mass assignment for all columns

    protected function casts(): array
    {
        return [
            'is_paid'    => 'boolean',
            'is_active'  => 'boolean',
            'is_private' => 'boolean',
            'can_redeem' => 'boolean',
            'settings'   => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected array $schemalessAttributes = [
        'settings',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Exam $exam) {
            if (empty($exam->code)) {
                $exam->code = 'exam_' . Str::lower(Str::random(11));
            }
            // Default status on creation if not set
            if (empty($exam->status)) {
                $exam->status = self::STATUS_DRAFT;
            }
        });
    }

    public function updateMeta(): void
    {
        $this->total_questions = $this->questions()->count();

        // Calculate Total Duration
        // 1. Sum up all section durations
        $sectionDurationSum = $this->examSections()->sum('total_duration');

        // 2. If sum is 0 (Auto Mode), sum up all question default times
        if ($sectionDurationSum == 0) {
            $this->total_duration = $this->questions()->sum('default_time');
        } else {
            $this->total_duration = $sectionDurationSum;
        }

        $this->total_marks = $this->examSections()->sum('total_marks');
        $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function examSections(): HasMany
    {
        return $this->hasMany(ExamSection::class);
    }

    public function examSchedules(): HasMany
    {
        return $this->hasMany(ExamSchedule::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exam_questions', 'exam_id', 'question_id')
            ->withPivot('exam_section_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    public function microCategory(): BelongsTo
    {
        return $this->belongsTo(MicroCategory::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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

    public function scopePublished(Builder $query): void
    {
        // Now checks BOTH is_active AND status
        $query->where('is_active', true)->where('status', self::STATUS_PUBLISHED);
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', self::STATUS_PENDING);
    }

    public function scopeDraft(Builder $query): void
    {
        $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeIsPublic(Builder $query): void
    {
        $query->where('is_private', false);
    }

    public function scopeIsPrivate(Builder $query): void
    {
        $query->where('is_private', true);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'is_active', 'status', 'is_paid', 'is_private', 'total_marks', 'settings'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Exam has been {$eventName}");
    }
}
