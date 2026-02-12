<?php

namespace App\Models;

use App\Traits\SecureDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\SchemalessAttributes\SchemalessAttributesTrait;

class QuizSession extends Model
{
    /** @use HasFactory<\Database\Factories\QuizSessionFactory> */
    use HasFactory;
    use SchemalessAttributesTrait;
    use SoftDeletes;
    use SecureDeletes;
    use LogsActivity;

    /**
     * The attributes that aren't mass assignable.
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast (Laravel 11/12 Method Style).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'completed_at' => 'datetime',
            'current_question' => 'integer',
            'total_questions' => 'integer',
            'total_answered' => 'integer',
            'score' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Define which columns are schemaless.
     */
    protected array $schemalessAttributes = [
        'results',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (QuizSession $quizSession) {
            // Har session ke liye unique UUID generate karna tracking ke liye best hai
            if (empty($quizSession->code)) {
                $quizSession->code = (string) Str::uuid();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (Modern Syntax)
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate percentage score automatically.
     */
    protected function percentage(): Attribute
    {
        return Attribute::make(
            get: fn () => ($this->total_marks > 0)
                ? round(($this->score / $this->total_marks) * 100, 2)
                : 0,
        );
    }

    /**
     * Human-readable time taken.
     */
    protected function timeTakenHuman(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->completed_at) return 'In Progress';
                return $this->starts_at->diffForHumans($this->completed_at, true);
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */



    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Questions in this session with pivot data.
     * Pivot table stores exactly what the user did during the test.
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'quiz_session_questions')
            ->withPivot([
                'status',
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
            ->withTrashed(); // Agar question delete bhi ho jaye, report bani rahe
    }

    public function quizSchedule(): BelongsTo
    {
        return $this->belongsTo(QuizSchedule::class);
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

    public function scopeCompleted(Builder $query): void
    {
        $query->where('status', 'completed');
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG CONFIG
    |--------------------------------------------------------------------------
    */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'score', 'completed_at', 'results'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Quiz Session was {$eventName}");
    }
}
