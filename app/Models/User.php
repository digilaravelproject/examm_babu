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
use Illuminate\Support\Str; // Added for Random String
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
     * The "booted" method of the model.
     * Handles Auto-Generation of Referral Codes.
     */
    protected static function booted()
    {
        static::creating(function ($user) {

            // --- FUTURE INSTRUCTOR LOGIC HERE ---
            // if (! $user->hasRole('instructor')) return;
            // ------------------------------------

            if (empty($user->referral_code)) {
                // Unique Code Generate karega: EXAM-A1B2C
                $user->referral_code = self::generateUniqueReferralCode();
            }
        });
    }

    /**
     * Helper to generate a truly unique referral code
     */
    public static function generateUniqueReferralCode()
    {
        do {
            $code = 'EXAM-' . strtoupper(Str::random(6));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

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
        'referral_code',        // Added
        'is_active',
        'email_verified_at',
        'verification_code',           // Added for OTP
        'verification_code_expires_at', // Added for OTP Expiry
        'profile_photo_path',
        'current_team_id',
        'wallet_balance',
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
            // 'preferences'               => 'array',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (Modern Laravel Syntax)
    |--------------------------------------------------------------------------
    */
protected function profilePhotoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Agar DB me photo path hai to storage URL return karo
                if ($this->profile_photo_path) {
                    return asset('storage/' . $this->profile_photo_path);
                }

                // Warna UI Avatars se default image generate karo name ke base par
                $name = trim($this->first_name . ' ' . $this->last_name);
                return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=7F9CF5&background=EBF4FF';
            },
        );
    }
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    /**
     * Calculates Wallet Balance dynamically from Transactions table
     */
    protected function walletBalance(): Attribute
    {
        return Attribute::make(
            // Hum sum('amount') kar rahe hain. Credit positive hoga, Debit negative hoga.
            get: fn() => $this->walletTransactions()->sum('amount'),
        );
    }

    protected function roleId(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->roles->first()?->name,
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
            ->withPivot('joined_at');
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

    // --- NEW REFERRAL SYSTEM RELATIONSHIPS ---

    /**
     * Users referred by this user (My Referrals)
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    /**
     * Wallet Passbook (Credits and Debits)
     */
    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Withdrawal Requests sent to Admin
     */
    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
    }
public function referralSetting()
{
    return $this->hasOne(UserReferralSetting::class);
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
            ->logOnly(['first_name', 'last_name', 'user_name', 'email', 'mobile', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "User {$this->user_name} account has been {$eventName}");
    }
}
