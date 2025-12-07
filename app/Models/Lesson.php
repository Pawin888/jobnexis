<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $primaryKey = 'l_id';

    protected $fillable = [
        'l_name',
        'l_description',
        'l_status',
        'l_index',
        'l_c_id', // FK to courses
    ];

    // สถานะ
    public function isPubliced()
    {
        return $this->getAttribute('l_status') === 'open';
    }

    public function isClosed()
    {
        return $this->getAttribute('l_status') === 'closed';
    }

    public function isDraft()
    {
        return $this->getAttribute('l_status') === 'draft';
    }

    // ความสัมพันธ์กับคอร์ส
    public function course()
    {
        return $this->belongsTo(Course::class, 'l_c_id');
    }

    // ความสัมพันธ์กับ Media (หลายสื่อ)
    public function medias()
    {
        return $this->hasMany(Media::class, 'm_l_id', 'l_id');
    }

    // ความสัมพันธ์กับ MediaFile ผ่าน Media
    public function attachments()
    {
        return $this->hasManyThrough(
            MediaFile::class,   // Model ปลายทาง
            Media::class,       // Model ตัวกลาง
            'm_l_id',           // Foreign key ของ Media -> Lesson
            'mf_m_id',          // Foreign key ของ MediaFile -> Media
            'l_id',             // Local key ของ Lesson
            'm_id'              // Local key ของ Media
        );
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'e_l_id');
    }
}
