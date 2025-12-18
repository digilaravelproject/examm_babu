<?php

namespace App\Models;

use App\Traits\SecureDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\SchemalessAttributes\SchemalessAttributesTrait;

class ExamSession extends Model
{
    /** @use HasFactory<\Database\Factories\ExamSessionFactory> */
    use HasFactory;
    use SchemalessAttributesTrait;
    use SoftDeletes;
    use SecureDeletes;
    use LogsActivity;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $guarded = [];

    /**
     * Define schemaless attributes column.
     */
    protected array $schemalessAttributes = [
        'results',
    ];

    /**
     * Get the attributes that should be cast (Laravel 11/12 Method Style).
     */
    protected function casts(): array
    {
        return [
            'starts_at'     => 'datetime',
            'ends_at'       => 'datetime',
            'completed_at'  => 'datetime',
            'exam_sections' => 'array',
            'results'       => 'array',
            'created_at'    => 'datetime',
            'updated_at'    => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (ExamSession $examSession) {
            // Fixed variable name from $category to $examSession
            if (empty($examSession->code)) {
                $examSession->code = (string) Str::uuid();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(ExamSection::class, 'exam_session_sections')
            ->withPivot([
                'sno',
                'name',
                'status',
                'section_id',
                'starts_at',
                'ends_at',
                'total_time_taken',
                'current_question',
                'results'
            ])
            ->withTimestamps();
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exam_session_questions')
            ->withPivot([
                'status',
                'exam_section_id',
                'original_question',
                'options',
                'is_correct',
                'time_taken',
                'user_answer',
                'correct_answer',
                'marks_earned',
                'marks_deducted'
            ])
            ->withTimestamps()
            ->withTrashed();
    }

    public function examSchedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopePending(Builder $query): void
    {
        $query->where('status', 'started');
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG CONFIG
    |--------------------------------------------------------------------------
    */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'total_marks', 'starts_at', 'completed_at', 'results'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Exam Session has been {$eventName}");
    }
}
