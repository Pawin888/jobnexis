<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exam_id',
        'score',
        'total_questions',
        'required',
        'passed',
        'percentage',
        'answers',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'answers' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'e_id');
    }
}

