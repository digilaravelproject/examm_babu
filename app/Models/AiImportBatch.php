<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property int $user_id
 * @property int $topic_id
 * @property string $status
 * @property string $pdf_path
 * @property int $start_page
 * @property int $end_page
 * @property int $questions_count
 * @property int $progress
 * @property string|null $message
 * @property string|null $error_details
 * @property array|null $metadata
 * @method static \Illuminate\Database\Eloquent\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static \App\Models\AiImportBatch|null find($id, $columns = ['*'])
 * @method static \App\Models\AiImportBatch create(array $attributes = [])
 * @method bool update(array $attributes = [], array $options = [])
 * @method bool|null delete()
 */
class AiImportBatch extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'topic_id',
        'status',
        'pdf_path',
        'start_page',
        'end_page',
        'questions_count',
        'progress',
        'message',
        'error_details',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'start_page' => 'integer',
        'end_page' => 'integer',
        'questions_count' => 'integer',
        'progress' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }
}
