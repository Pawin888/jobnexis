<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recruitment extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_LABELS = [
        'full-time' => 'เต็มเวลา (Full-time)',
        'part-time' => 'พาร์ทไทม์ (Part-time)',
        'intern' => 'ฝึกงาน (Internship)',
        'freelance' => 'ฟรีแลนซ์ (Freelance)',
    ];

    public const WORK_MODE_LABELS = [
        'onsite' => 'เข้าออฟฟิศ (Work on Site)',
        'remote' => 'ทำที่บ้าน (Work from Home)',
        'hybrid' => 'ผสมผสาน (Hybrid Work)',
        'distributed' => 'ทำที่ไหนก็ได้ (Distributed Work)',
    ];

    protected $table = 'recruitments';
    protected $primaryKey = 'rc_id';

    protected $fillable = [
        'rc_title',
        'rc_description',
        'rc_requirements',
        'rc_gender',
        'rc_education_level',
        'rc_experience_level',
        'rc_salary',
        'rc_location_text',
        'rc_location_link',
        'rc_type',
        'rc_status',
        'rc_work_mode',
        'rc_posted_at',
        'rc_expire_at',
        'rc_application_url',
        'rc_views',
        'rc_u_id',
    ];

    protected $casts = [
        'rc_posted_at' => 'datetime',
        'rc_expire_at' => 'date',
    ];

    public static function typeLabel(?string $value): string
    {
        return self::TYPE_LABELS[$value] ?? ($value ?: 'ไม่ระบุ');
    }

    public static function workModeLabel(?string $value): string
    {
        return self::WORK_MODE_LABELS[$value] ?? 'ไม่ระบุ';
    }

    public function getWorkModeValuesAttribute(): array
    {
        return collect(explode(',', (string) ($this->attributes['rc_work_mode'] ?? '')))
            ->map(fn ($mode) => trim($mode))
            ->filter()
            ->values()
            ->all();
    }

    public function getWorkModeLabelsAttribute(): array
    {
        $values = $this->work_mode_values;

        if (empty($values)) {
            return ['ไม่ระบุ'];
        }

        return array_map(fn ($mode) => self::workModeLabel($mode), $values);
    }

    public function getTypeValuesAttribute(): array
    {
        return collect(explode(',', (string) ($this->attributes['rc_type'] ?? '')))
            ->map(fn ($type) => trim($type))
            ->filter()
            ->values()
            ->all();
    }

    public function getTypeLabelsAttribute(): array
    {
        $values = $this->type_values;

        if (empty($values)) {
            return ['ไม่ระบุ'];
        }

        return array_map(fn ($type) => self::typeLabel($type), $values);
    }

    public function getTypeTextAttribute(): string
    {
        return implode(', ', $this->type_labels);
    }

    public function getWorkModeLabelAttribute(): string
    {
        return implode(', ', $this->work_mode_labels);
    }

    /**
     * เจ้าของประกาศ (User)
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'rc_u_id');
    }

    /**
     * Recruitment → RecruitmentSkills
     */
    public function skills()
    {
        return $this->hasMany(
            RecruitmentSkill::class,
            'rc_id',
            'rc_id'
        );
    }

    /**
     * Recruitment → MasterSkills (many-to-many)
     * พร้อม pivot: proficiency_level, master_skill_group_id
     */
    public function masterSkills()
    {
        return $this->belongsToMany(
            MasterSkill::class,
            'recruitment_skills',
            'rc_id',
            'master_skill_id'
        )
        ->withPivot('proficiency_level', 'master_skill_group_id')
        ->withTimestamps();
    }

    /**
     * Scope: ของ user คนนี้
     */
    public function scopeOwnedBy($q, $userId)
    {
        return $q->where('rc_u_id', $userId);
    }

    /**
     * Scope: ยังเปิดอยู่และไม่หมดอายุ
     */
    public function scopeOpen($q)
    {
        return $q->where('rc_status', 'open')
                 ->where(function ($qq) {
                     $qq->whereNull('rc_expire_at')
                        ->orWhereDate('rc_expire_at', '>=', now()->toDateString());
                 });
    }

    /**
     * Recruitment → JobApplications
     */
    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'recruitment_id', 'rc_id');
    }

    public function languages()
    {
        return $this->hasMany(RecruitmentLanguage::class, 'rc_id', 'rc_id');
    }

    public function recruitmentSkills()
    {
        return $this->hasMany(RecruitmentSkill::class, 'rc_id', 'rc_id');
    }
}
