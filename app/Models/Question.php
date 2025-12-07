<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $primaryKey = "q_id";
    protected $fillable = [
        'q_question',
        'q_answer1',
        'q_answer2',
        'q_answer3',
        'q_answer4',
        'q_correct_answer',
        'q_e_id',
    ];
    protected $casts = [
        'q_correct_answer' => 'integer',
    ];
    public function exam()
    {
        return $this->belongsTo(Exam::class, 'q_e_id');
    }
}
