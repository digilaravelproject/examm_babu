<?php

namespace App\Models;

use App\Filters\QueryFilter;
use App\Settings\LocalizationSettings;
use App\Traits\SecureDeletes;
use Carbon\Carbon;
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

class QuizSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\QuizScheduleFactory> */
    use HasFactory;
    use SoftDeletes;
    use SecureDeletes;
    use LogsActivity;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'quiz_schedules';

    /**
     * The attributes that aren't mass assignable.
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The accessors to append to the model's array form.
     * @var list<string>
     */
    protected $appends = [
        'starts_at',
        'ends_at',
        'starts_at_formatted',
        'ends_at_formatted',
        'timezone'
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
            'is_active'  => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (QuizSchedule $quizSchedule) {
            if (empty($quizSchedule->code)) {
                $quizSchedule->code = 'qsd_' . Str::lower(Str::random(11));
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (Modern Laravel 12 Syntax)
    |--------------------------------------------------------------------------
    */

    /**
     * Helper to get Localization Settings
     */
    private function getTz(): string
    {
        return app(LocalizationSettings::class)->default_timezone ?? config('app.timezone');
    }

    protected function startsAt(): Attribute
    {
        return Attribute::make(
            get: fn () => Carbon::parse("{$this->start_date->format('Y-m-d')} {$this->start_time}", $this->getTz()),
        );
    }

    protected function endsAt(): Attribute
    {
        return Attribute::make(
            get: fn () => Carbon::parse("{$this->end_date->format('Y-m-d')} {$this->end_time}", $this->getTz()),
        );
    }

    protected function startsAtFormatted(): Attribute
    {
        return Attribute::make(
            get: function () {
                $format = ($this->schedule_type === 'fixed') ? 'D, M jS, Y' : 'D, M jS, Y, h:i A';
                return $this->starts_at->format($format);
            }
        );
    }

    protected function endsAtFormatted(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->schedule_type === 'fixed') {
                    return $this->starts_at->format('h:i A') . ' - ' . $this->ends_at->format('h:i A');
                }
                return $this->ends_at->format('D, M jS, Y, h:i A');
            }
        );
    }

    protected function timezone(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getTz(),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */



    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'user_group_quiz_schedules', 'quiz_schedule_id', 'user_group_id')
            ->withTimestamps();
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(QuizSession::class);
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
        $query->where('status', 'active');
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG CONFIG
    |--------------------------------------------------------------------------
    */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['start_date', 'end_date', 'start_time', 'end_time', 'status', 'schedule_type'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Quiz Schedule has been {$eventName}");
    }
}
