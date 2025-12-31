<?php

namespace App\Models;

use App\Filters\QueryFilter;
use App\Traits\SecureDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Znck\Eloquent\Traits\BelongsToThrough;

class Question extends Model
{
    /** @use HasFactory<\Database\Factories\QuestionFactory> */
    use HasFactory;
    use SoftDeletes;
    use SecureDeletes;
    use BelongsToThrough;
    use LogsActivity;

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
            'options'            => 'array',
            'preferences'        => 'object',
            'has_attachment'     => 'boolean',
            'attachment_options' => 'object',
            'solution_video'     => 'object',
            'is_active'          => 'boolean',
            'created_at'         => 'datetime',
            'updated_at'         => 'datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Question $question) {
            if (empty($question->code)) {
                $question->code = 'que_' . Str::lower(Str::random(11));
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & MUTATORS (Modern Syntax)
    |--------------------------------------------------------------------------
    */

    /**
     * Handle Correct Answer serialization.
     * Note: In modern Laravel, using 'array' cast is better,
     * but we keep serialize for legacy data compatibility.
     */
    protected function correctAnswer(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? unserialize($value) : null,
            set: fn($value) => serialize($value),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function questionType(): BelongsTo
    {
        return $this->belongsTo(QuestionType::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }

    /**
     * Through relation: Question -> Skill -> Section
     */
    public function section()
    {
        return $this->belongsToThrough(Section::class, Skill::class);
    }

    public function difficultyLevel(): BelongsTo
    {
        return $this->belongsTo(DifficultyLevel::class);
    }

    public function comprehensionPassage(): BelongsTo
    {
        return $this->belongsTo(ComprehensionPassage::class);
    }

    public function practiceSets(): BelongsToMany
    {
        return $this->belongsToMany(PracticeSet::class, 'practice_set_questions', 'question_id', 'practice_set_id')
            ->withTimestamps();
    }

    public function practiceSessions(): BelongsToMany
    {
        return $this->belongsToMany(PracticeSession::class, 'practice_session_questions')
            ->withPivot(['status', 'original_question', 'is_correct', 'time_taken', 'options', 'user_answer', 'correct_answer', 'points_earned'])
            ->withTimestamps();
    }

    public function quizzes(): BelongsToMany
    {
        return $this->belongsToMany(Quiz::class, 'quiz_questions', 'question_id', 'quiz_id')
            ->withTimestamps();
    }

    public function quizSessions(): BelongsToMany
    {
        return $this->belongsToMany(QuizSession::class, 'quiz_session_questions')
            ->withPivot(['status', 'original_question', 'options', 'is_correct', 'time_taken', 'user_answer', 'correct_answer', 'marks_earned', 'marks_deducted'])
            ->withTimestamps();
    }

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'exam_questions', 'question_id', 'exam_id');
    }

    public function examSessions(): BelongsToMany
    {
        return $this->belongsToMany(ExamSession::class, 'exam_session_questions')
            ->withPivot(['status', 'exam_section_id', 'original_question', 'options', 'is_correct', 'time_taken', 'user_answer', 'correct_answer', 'marks_earned', 'marks_deducted'])
            ->withTimestamps();
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
    public function linkedExams()
    {
        // Note: Yahan 'withTimestamps()' NAHI lagaya hai
        return $this->belongsToMany(Exam::class, 'exam_questions', 'question_id', 'exam_id');
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
            ->logOnly(['question', 'question_type_id', 'topic_id', 'difficulty_level_id', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Question has been {$eventName}");
    }
}
