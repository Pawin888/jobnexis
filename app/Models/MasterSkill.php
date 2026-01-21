<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MasterSkill extends Model
{
    protected $table = 'master_skills';

    protected $fillable = [
        'esco_uri',
        'esco_code',
        'name',
        'description',
        'level',
        'source',
        'is_active',
    ];

    /**
     * Skill → Skill Groups (ESCO mapping)
     */
    public function skillGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            MasterSkillGroup::class,
            'master_skill_group_skill',
            'master_skill_id',
            'master_skill_group_id'
        );
    }

    public function skillGroup()
    {
        return $this->skillGroups()->first();
    }
}
