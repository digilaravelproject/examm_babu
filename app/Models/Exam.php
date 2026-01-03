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

    protected $table = 'exams';
    protected $guarded = [];

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
        });
    }

    public function updateMeta(): void
    {
        $this->total_questions = $this->questions()->count();
        $this->total_duration = $this->examSections()->sum('total_duration');
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'is_active', 'is_paid', 'is_private', 'total_marks', 'settings'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Exam has been {$eventName}");
    }
}
