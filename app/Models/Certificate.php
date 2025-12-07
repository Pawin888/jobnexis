<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $primaryKey = 'cer_id';

    protected $fillable = [
        'cer_name',
        'cer_image_path',
        'cer_institute_name',
        'cer_ref_number',
        'cer_from_lesson',
        'cer_u_id',
        'cer_c_id',
        'cer_publiced',
    ];

    // ความสัมพันธ์กับ User
    public function user()
    {
        return $this->belongsTo(User::class, 'cer_u_id', 'id');
    }

    // ความสัมพันธ์กับ Course
    public function course()
    {
        return $this->belongsTo(Course::class, 'cer_c_id', 'c_id');
    }
}
