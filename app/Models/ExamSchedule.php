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

class ExamSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\ExamScheduleFactory> */
    use HasFactory;
    use SoftDeletes;
    use SecureDeletes;
    use LogsActivity;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'exam_schedules';

    protected $guarded = [];

    /**
     * Get the attributes that should be cast (Laravel 11/12 Method Style).
     */
    protected function casts(): array
    {
        return [
            // Assuming these are strictly date columns based on legacy code usage
            'start_date' => 'date:Y-m-d',
            'end_date'   => 'date:Y-m-d',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
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
        static::creating(function (ExamSchedule $examSchedule) {
            if (empty($examSchedule->code)) {
                $examSchedule->code = 'esd_' . Str::lower(Str::random(11));
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
        return $this->belongsTo(Exam::class);
    }

    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'user_group_exam_schedules', 'exam_schedule_id', 'user_group_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
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
    | ACCESSORS & MUTATORS (Modern Syntax)
    |--------------------------------------------------------------------------
    */

    /**
     * Combine start_date and start_time into a Carbon instance with app timezone.
     */
    protected function startsAt(): Attribute
    {
        return Attribute::make(
            get: fn () => Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $this->start_date->format('Y-m-d') . ' ' . $this->start_time,
                app(LocalizationSettings::class)->default_timezone
            )
        );
    }

    /**
     * Combine end_date and end_time into a Carbon instance with app timezone.
     */
    protected function endsAt(): Attribute
    {
        return Attribute::make(
            get: fn () => Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $this->end_date->format('Y-m-d') . ' ' . $this->end_time,
                app(LocalizationSettings::class)->default_timezone
            )
        );
    }

    protected function startsAtFormatted(): Attribute
    {
        return Attribute::make(
            get: function () {
                $date = $this->starts_at; // Uses the accessor above

                if ($this->schedule_type === 'fixed') {
                    return $date->format('D, M jS, Y');
                }

                return $date->format('D, M jS, Y, h:i A');
            }
        );
    }

    protected function endsAtFormatted(): Attribute
    {
        return Attribute::make(
            get: function () {
                $endDate = $this->ends_at; // Uses the accessor above

                if ($this->schedule_type === 'fixed') {
                    $startDate = $this->starts_at;
                    return $startDate->format('h:i A') . ' - ' . $endDate->format('h:i A');
                }

                return $endDate->format('D, M jS, Y, h:i A');
            }
        );
    }

    protected function timezone(): Attribute
    {
        return Attribute::make(
            get: fn () => app(LocalizationSettings::class)->default_timezone
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG CONFIG
    |--------------------------------------------------------------------------
    */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['exam_id', 'schedule_type', 'start_date', 'end_date', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Exam Schedule has been {$eventName}");
    }
}
