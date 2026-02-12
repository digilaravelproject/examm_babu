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

class Video extends Model
{
    /** @use HasFactory<\Database\Factories\VideoFactory> */
    use HasFactory;
    use SoftDeletes;
    use SecureDeletes;
    use BelongsToThrough;
    use LogsActivity;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'videos';

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
        'video_type_name'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'preferences' => 'array', // JSON storage ke liye array best hai
            'is_paid' => 'boolean',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Video $video) {
            if (empty($video->code)) {
                $video->code = 'video_' . Str::lower(Str::random(11));
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (Modern Syntax)
    |--------------------------------------------------------------------------
    */

    protected function videoTypeName(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->video_type) {
                'mp4'     => 'MP4 Video',
                'youtube' => 'Youtube Video',
                'vimeo'   => 'Vimeo Video',
                default   => 'Video',
            },
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    /**
     * Through relation using znck/belongs-to-through
     */
    public function section()
    {
        return $this->belongsToThrough(Section::class, Skill::class);
    }

    public function difficultyLevel(): BelongsTo
    {
        return $this->belongsTo(DifficultyLevel::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function subCategories(): BelongsToMany
    {
        return $this->belongsToMany(SubCategory::class, 'practice_videos', 'video_id', 'sub_category_id')
            ->withPivot('sort_order')
            ->withTimestamps();
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
            ->logOnly(['title', 'video_type', 'is_active', 'is_paid'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Video has been {$eventName}");
    }
}
