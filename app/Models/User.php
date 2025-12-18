<?php

namespace App\Models;

use App\Filters\QueryFilter;
use App\Traits\SecureDeletes;
use App\Traits\SubscriptionTrait;
use App\Traits\SyllabusTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;
use Spatie\SchemalessAttributes\SchemalessAttributesTrait;

/**
 * @property \Spatie\SchemalessAttributes\SchemalessAttributes $preferences
 * @method static Builder withPreferencesAttributes()
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;
    use SoftDeletes;
    use SecureDeletes;
    use SubscriptionTrait;
    use SyllabusTrait;
    use LogsActivity;
    use SchemalessAttributesTrait;

    /**
     * The attributes that are mass assignable.
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'user_name',
        'mobile',
        'email',
        'password',
        'is_active',
        // 'preferences', // Optional: Fillable mein rakh sakte ho, but Spatie Trait handle karta hai mostly
        'email_verified_at',
        'verification_code',
        'profile_photo_path',
        'current_team_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'verification_code',
    ];

    /**
     * Attributes for Schemaless functionality.
     */
    protected array $schemalessAttributes = [
        'preferences',
    ];

    /**
     * The accessors to append to the model's array form.
     * @var list<string>
     */
    protected $appends = [
        'profile_photo_url',
        'full_name',
        'role_id',
        'wallet_balance',
    ];

    /**
     * Get the attributes that should be cast (Laravel 11/12 Style).
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'            => 'datetime',
            'mobile_verified_at'           => 'datetime',
            'verification_code_expires_at' => 'datetime',
            'password'                     => 'hashed',
            'is_active'                    => 'boolean',
            // 'preferences'               => 'array', // <--- REMOVE THIS LINE (Very Important)
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (Modern Laravel Syntax)
    |--------------------------------------------------------------------------
    */

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    protected function walletBalance(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->balance,
        );
    }

    protected function roleId(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->roles->first()?->name,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'user_group_users', 'user_id', 'user_group_id')
            ->withPivot('joined_at')
            ->withTimestamps();
    }

    public function practiceSessions(): HasMany
    {
        return $this->hasMany(PracticeSession::class);
    }

    public function quizSessions(): HasMany
    {
        return $this->hasMany(QuizSession::class);
    }

    public function examSessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->with(['payment' => function ($query) {
            $query->where('status', 'success');
        }]);
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

    public function scopeWithPreferencesAttributes(): Builder
    {
        // Ab yeh error nahi dega kyunki 'casts' se array hata diya hai
        // aur upar PHPDoc mein type define kar diya hai.
        return $this->preferences->modelScope();
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG CONFIG
    |--------------------------------------------------------------------------
    */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'user_name','email', 'mobile', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "User {$this->user_name} account has been {$eventName}");
    }
}
