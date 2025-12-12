<?php
// app/Models/Quiz.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quiz extends Model
{
    use HasFactory;

    protected $table = 'quizzes'; // adjust if your table name differs

    protected $guarded = [];

    // Sample relationships — update or remove if your schema differs
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function quizType()
    {
        return $this->belongsTo(\App\Models\QuizType::class, 'type_id');
    }
}
