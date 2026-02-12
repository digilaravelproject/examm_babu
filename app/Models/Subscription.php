<?php

namespace App\Models;

use App\Filters\QueryFilter;
use App\Settings\LocalizationSettings;
use App\Traits\SecureDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Subscription extends Model
{
    /** @use HasFactory<\Database\Factories\SubscriptionFactory> */
    use HasFactory;
    use SoftDeletes;
    use SecureDeletes;
    use LogsActivity;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'subscriptions';

    /**
     * The attributes that aren't mass assignable.
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The accessors to append to the model's array form.
     * @var list<string>
     */
    protected $appends = ['starts', 'ends'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Subscription $subscription) {
            if (empty($subscription->code)) {
                $subscription->code = 'subscription_' . Str::lower(Str::random(11));
            }
        });

        // Jab subscription create, update ya delete ho, toh user ka cache clear kar do
        static::saved(fn (Subscription $subscription) => $subscription->clearUserSubscriptionCache());
        static::deleted(fn (Subscription $subscription) => $subscription->clearUserSubscriptionCache());
    }

    /**
     * Helper to check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->ends_at?->isFuture() ?? false;
    }

    /**
     * Logic to clear the cache defined in SubscriptionTrait.
     */
    protected function clearUserSubscriptionCache(): void
    {
        if ($this->user_id) {
            $user = $this->user;
            if ($user && method_exists($user, 'flushSubscriptionCache')) {
                $user->flushSubscriptionCache($this->category_id);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (Modern Laravel 12 Syntax)
    |--------------------------------------------------------------------------
    */

    protected function starts(): Attribute
    {
        return Attribute::make(
            get: function () {
                $localization = app(LocalizationSettings::class);
                return $this->starts_at?->timezone($localization->default_timezone)->toFormattedDateString();
            }
        );
    }

    protected function ends(): Attribute
    {
        return Attribute::make(
            get: function () {
                $localization = app(LocalizationSettings::class);
                return $this->ends_at?->timezone($localization->default_timezone)->toFormattedDateString();
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Polymorphic relation to Category or SubCategory.
     */
    public function category(): MorphTo
    {
        return $this->morphTo();
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
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

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG CONFIG
    |--------------------------------------------------------------------------
    */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['plan_id', 'user_id', 'starts_at', 'ends_at', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Subscription has been {$eventName}");
    }
}
