<?php

namespace App\Models;

use App\Filters\QueryFilter;
use App\Traits\SecureDeletes;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Tag extends Model
{
    /** @use HasFactory<\Database\Factories\TagFactory> */
    use HasFactory;
    use Sluggable;
    use SoftDeletes;
    use SecureDeletes;
    use LogsActivity;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'tags';

    /**
     * The attributes that aren't mass assignable.
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Sluggable configuration.
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS (Polymorphic Many-to-Many)
    |--------------------------------------------------------------------------
    */



    /**
     * Get all of the questions that are assigned this tag.
     */
    public function questions(): MorphToMany
    {
        return $this->morphedByMany(Question::class, 'taggable');
    }

    /**
     * Get all of the lessons that are assigned this tag.
     */
    public function lessons(): MorphToMany
    {
        return $this->morphedByMany(Lesson::class, 'taggable');
    }

    /**
     * Get all of the videos that are assigned this tag.
     */
    public function videos(): MorphToMany
    {
        return $this->morphedByMany(Video::class, 'taggable');
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
            ->logOnly(['name', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Tag has been {$eventName}");
    }
}
