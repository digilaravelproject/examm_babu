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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\SchemalessAttributes\SchemalessAttributesTrait;

/**
 * @property \Spatie\SchemalessAttributes\SchemalessAttributes $data
 * @method static Builder withDataAttributes()
 */
class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
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
            // 'data' => 'array',  <-- YEH LINE REMOVE KAR DI HAI (Conflict kar rahi thi)
            'total_amount'     => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Define schemaless attributes column.
     */
    protected array $schemalessAttributes = [
        'data',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
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

    public function scopeWithDataAttributes(): Builder
    {
        return $this->data->modelScope();
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & MUTATORS (Modern Syntax)
    |--------------------------------------------------------------------------
    */

    /**
     * Parse payment date and convert to application default timezone.
     */
    protected function paymentDate(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) return null;

                $localization = app(LocalizationSettings::class);
                return Carbon::parse($value)->timezone($localization->default_timezone);
            }
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
            ->logOnly(['payment_id', 'total_amount', 'method', 'status', 'user_id', 'plan_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Payment has been {$eventName}");
    }
}
