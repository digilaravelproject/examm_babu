<?php

namespace App\Models;

use App\Traits\SecureDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ExamSection extends Model
{
    /** @use HasFactory<\Database\Factories\ExamSectionFactory> */
    use HasFactory;
    use SoftDeletes;
    use SecureDeletes;
    use LogsActivity;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $guarded = [];

    /**
     * Get the attributes that should be cast (Laravel 11/12 Method Style).
     */
   protected function casts(): array
{
    return [
        'auto_duration'           => 'boolean',
        'auto_grading'            => 'boolean',
        'enable_negative_marking' => 'boolean',
        'enable_section_cutoff'   => 'boolean',
        'assign_examiner'         => 'boolean',
        'examined'                => 'boolean',
        'approved'                => 'boolean',

        'total_questions' => 'integer',
        'total_marks'     => 'float',
        'correct_marks'   => 'float',
        'negative_marks'  => 'float',
        'total_duration'  => 'integer',

        'examined_at' => 'datetime',
        'approved_at' => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];
}


    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Updates the meta information (questions count, marks, duration) for the section.
     */
    public function updateMeta(): void
    {
        $this->total_questions = $this->questions()->count();

        // Check settings from the related Exam model
        // Assuming 'settings' is a schemaless attribute or array on Exam
        $autoDuration = $this->exam->settings['auto_duration'] ?? true;
        $autoGrading = $this->exam->settings['auto_grading'] ?? true;

        if ($autoDuration) {
            $this->total_duration = $this->questions()->sum('default_time');
        }

        if ($autoGrading) {
            $this->total_marks = $this->questions()->sum('default_marks');
        } else {
            // Manual grading based on section-level correct marks
            $this->total_marks = $this->questions()->count() * $this->correct_marks;
        }

        $this->save();
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

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exam_questions', 'exam_section_id', 'question_id');
            // ->withTimestamps();
    }

    public function examSessions(): BelongsToMany
    {
        return $this->belongsToMany(ExamSession::class, 'exam_session_sections')
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG CONFIG
    |--------------------------------------------------------------------------
    */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'exam_id',
                'section_id',
                'total_questions',
                'total_marks',
                'total_duration',
                'enable_section_cutoff'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Exam Section has been {$eventName}");
    }
}
