<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Resume extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'birth_date',
        'gender',
        'email',
        'phone',
        'summary',
        'available_start_date',
        'preferred_location',
        'expected_salary',
        'is_visible',
        'profile_image',
    ];

    /**
     * Resume → ResumeSkills
     */
    public function resumeSkills(): HasMany
    {
        return $this->hasMany(ResumeSkill::class);
    }

    /**
     * Resume → Skills (many-to-many ผ่าน resume_skills)
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(
            MasterSkill::class,
            'resume_skills',
            'resume_id',
            'skill_id'
        )->withPivot('proficiency_level')
         ->withTimestamps();
    }
    // Work Experiences
    public function workExperiences()
    {
        return $this->hasMany(ResumeWorkExperience::class, 'resume_id'); 
    }

    // Educations
    public function educations()
    {
        return $this->hasMany(ResumeEducation::class, 'resume_id', 'id');
    }

    // Certificates
    public function certificates()
    {
        return $this->hasMany(ResumeCertificate::class, 'resume_id', 'id');
    }

    // Languages
    public function languages()
    {
        return $this->hasMany(ResumeLanguage::class, 'resume_id', 'id');
    }
}
