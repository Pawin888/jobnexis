<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $primaryKey = 'e_id';

    protected $fillable = [
        'e_name',
        'e_description',
        'e_skills',
        'pass_threshold',
        'e_l_id',
        'e_c_id',
        'e_index',
    ];

    protected $casts = [
        'e_skills' => 'array',
        'pass_threshold' => 'integer',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'e_l_id', 'l_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'e_c_id', 'c_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'q_e_id', 'e_id')->orderBy('q_id');
    }
}

