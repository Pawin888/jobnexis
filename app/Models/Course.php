<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $primaryKey = 'c_id';

    protected $fillable = [
        'c_name',
        'c_description',
        'c_status',
        'c_image',
        'c_code',
        'c_create_by_id',
        'c_create_at',
        'c_end_at',
    ];

    // ตรวจสอบสถานะ
    public function isPubliced()
    {
        return $this->getAttribute('c_status') === 'open';
    }

    public function isClosed()
    {
        return $this->getAttribute('c_status') === 'closed';
    }

    public function isDraft()
    {
        return $this->getAttribute('c_status') === 'draft';
    }

    public function isPending()
    {
        return $this->getAttribute('c_status') === 'pending';
    }

    public function coursesMember()
    {
        return $this->hasMany(CourseMember::class, 'cm_c_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'l_c_id');
    }

    public function certificate()
    {
        return $this->hasMany(Certificate::class, 'cer_c_id');
    }

    public function skills()
    {
        return $this->belongsToMany(
            Skill::class,
            'course_skill',  // ชื่อตาราง pivot
            'course_id',     // foreign key ของ Course ใน pivot
            'skill_id'       // foreign key ของ Skill ใน pivot
        );
    }

    // ฟังก์ชันช่วยนับสมาชิก (participants)
    public function getParticipantsAttribute()
    {
        return $this->coursesMember()->count();
    }

    // Accessor สำหรับรูปภาพ
    public function getImageAttribute()
    {
        return $this->c_image
            ? asset('storage/' . $this->c_image)
            : asset('image/wed-image/ai-robot.jpg');
    }

    // Accessor สำหรับแสดงสถานะเป็นข้อความภาษาไทย
    public function getStatusTextAttribute()
    {
        $map = [
            'open'   => 'เผยแพร่',
            'draft'  => 'ฉบับร่าง',
            'closed' => 'ไม่เผยแพร่',
            'pending'=> 'รออนุมัติ',
        ];

        return $map[$this->c_status] ?? 'ไม่ทราบสถานะ';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'c_create_by_id');
    }

    public function medias()
    {
        return $this->hasMany(Media::class, 'm_c_id', 'c_id');
    }

    // Method สำหรับลบคอร์สพร้อมข้อมูลที่เกี่ยวข้อง
    public function deleteWithRelated()
    {
        // ลบรูปภาพของคอร์ส
        if ($this->c_image && \Storage::disk('public')->exists($this->c_image)) {
            \Storage::disk('public')->delete($this->c_image);
        }

        // ลบสื่อการสอนทั้งหมด (รวม solo media)
        foreach ($this->medias as $media) {
            foreach ($media->files as $file) {
                if ($file->mf_path && \Storage::disk('public')->exists($file->mf_path)) {
                    \Storage::disk('public')->delete($file->mf_path);
                }
            }
            $media->files()->delete();
            $media->delete();
        }

        // ลบบทเรียนและสื่อในบทเรียน
        foreach ($this->lessons as $lesson) {
            foreach ($lesson->medias as $lessonMedia) {
                foreach ($lessonMedia->files as $file) {
                    if ($file->mf_path && \Storage::disk('public')->exists($file->mf_path)) {
                        \Storage::disk('public')->delete($file->mf_path);
                    }
                }
                $lessonMedia->files()->delete();
                $lessonMedia->delete();
            }
            $lesson->delete();
        }

        // ลบคอร์ส
        $this->delete();
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'e_c_id');
    }

}
