<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruitmentSkill extends Model
{
    protected $table = 'recruitment_skills';
    
    protected $fillable = [
        'rc_id',
        'master_skill_group_id',
        'master_skill_id',
        'proficiency_level',
    ];

    public $timestamps = true;

    /**
     * Recruitment Skill → Recruitment
     */
    public function recruitment(): BelongsTo
    {
        return $this->belongsTo(Recruitment::class, 'rc_id', 'rc_id');
    }

    /**
     * Recruitment Skill → Master Skill Group
     */
    public function skillGroup(): BelongsTo
    {
        return $this->belongsTo(MasterSkillGroup::class, 'master_skill_group_id');
    }

    /**
     * Recruitment Skill → Master Skill
     */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(MasterSkill::class, 'master_skill_id');
    }
}