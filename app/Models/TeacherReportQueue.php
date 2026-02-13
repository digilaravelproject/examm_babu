<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherReportQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_email',
        'student_name',
        'student_email',
        'exam_name',
        'user_id',
        'exam_session_id',
        'score',
        'total_marks',
        'result_url',
        'status'
    ];

    // Relation to User (Student)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relation to Exam Session
    public function examSession()
    {
        return $this->belongsTo(ExamSession::class);
    }
}
