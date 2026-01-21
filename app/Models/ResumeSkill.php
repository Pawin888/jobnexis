<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeSkill extends Model
{
    protected $table = 'resume_skills';

    protected $fillable = [
        'resume_id',
        'skill_group_id',
        'skill_id',
        'proficiency_level',
    ];

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(MasterSkill::class, 'skill_id');
    }

    public function skillGroup(): BelongsTo
    {
        return $this->belongsTo(MasterSkillGroup::class, 'skill_group_id');
    }
}
