<?php

namespace App\Models;

use App\Filters\QueryFilter;
use App\Settings\PaymentSettings;
use App\Traits\SecureDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property \Spatie\SchemalessAttributes\SchemalessAttributes $data
 * @method static Builder withDataAttributes()
 */
class Plan extends Model
{
    /** @use HasFactory<\Database\Factories\PlanFactory> */
    use HasFactory;
    use SoftDeletes;
    use SecureDeletes;
    use LogsActivity;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'plans';

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
            'has_trial'            => 'boolean',
            'has_discount'         => 'boolean',
            'feature_restrictions' => 'boolean',
            'is_popular'           => 'boolean',
            'is_active'            => 'boolean',
            'price'                => 'decimal:2',
            'discount_percentage'  => 'decimal:2',
            'created_at'           => 'datetime',
            'updated_at'           => 'datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Plan $plan) {
            // Fixed variable name from $subCategory to $plan
            if (empty($plan->code)) {
                $plan->code = 'plan_' . Str::lower(Str::random(11));
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function category(): MorphTo
    {
        return $this->morphTo();
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'plan_features', 'plan_id', 'feature_id')
            ->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
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
    | ACCESSORS & MUTATORS (Modern Syntax)
    |--------------------------------------------------------------------------
    */

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->category->name} {$this->name} - {$this->duration} Months Plan"
        );
    }

    /**
     * Formats the base price using global settings.
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => formatPrice(
                $this->price,
                app(PaymentSettings::class)->currency_symbol,
                app(PaymentSettings::class)->currency_symbol_position
            )
        );
    }

    /**
     * Calculates the price after discount.
     * Note: Returns 0 if no discount is applicable (preserving original logic).
     */
    protected function discountedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->has_discount
                ? $this->price - ($this->price * $this->discount_percentage / 100)
                : 0
        );
    }

    protected function formattedDiscountedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => formatPrice(
                $this->discounted_price,
                app(PaymentSettings::class)->currency_symbol,
                app(PaymentSettings::class)->currency_symbol_position
            )
        );
    }

    protected function totalPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price * $this->duration
        );
    }

    protected function formattedTotalPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => formatPrice(
                $this->total_price,
                app(PaymentSettings::class)->currency_symbol,
                app(PaymentSettings::class)->currency_symbol_position
            )
        );
    }

    protected function totalDiscountedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->has_discount
                ? $this->discounted_price * $this->duration
                : 0
        );
    }

    protected function formattedTotalDiscountedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => formatPrice(
                $this->total_discounted_price,
                app(PaymentSettings::class)->currency_symbol,
                app(PaymentSettings::class)->currency_symbol_position
            )
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
            ->logOnly(['name', 'price', 'discount_percentage', 'is_active', 'is_popular'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Subscription Plan has been {$eventName}");
    }
}
