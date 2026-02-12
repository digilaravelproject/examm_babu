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

class PracticeSession extends Model
{
    /** @use HasFactory<\Database\Factories\PracticeSessionFactory> */
    use HasFactory;
    use SoftDeletes;
    use SecureDeletes;
    use SchemalessAttributesTrait;
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
            'completed_at' => 'datetime',
            'results'      => 'array',
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
        ];
    }

    /**
     * Define schemaless attributes column.
     */
    protected array $schemalessAttributes = [
        'results',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (PracticeSession $practiceSession) {
            if (empty($practiceSession->code)) {
                $practiceSession->code = (string) Str::uuid();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * A practice session belongs to a practice set.
     */
    public function practiceSet(): BelongsTo
    {
        return $this->belongsTo(PracticeSet::class, 'practice_set_id');
    }

    /**
     * A practice session belongs to a user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Questions associated with this session.
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'practice_session_questions')
            ->withPivot([
                'status',
                'is_correct',
                'time_taken',
                'original_question',
                'options',
                'user_answer',
                'correct_answer',
                'points_earned'
            ])
            ->withTimestamps()
            ->withTrashed();
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include pending (started) sessions.
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
            ->logOnly(['status', 'total_points', 'completed_at', 'results'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Practice Session has been {$eventName}");
    }
}
