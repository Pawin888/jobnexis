<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseMember extends Model
{
    use HasFactory;
    protected $table = "course_members";
    protected $primaryKey = 'cm_id';
    protected $fillable = [
        'cm_c_id',
        'cm_u_id',
        'cm_passed',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'cm_u_id');
    }
    public function courses()
    {
        return $this->belongsTo(Course::class, 'cm_c_id');
    }
}

