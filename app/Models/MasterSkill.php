<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function skillGroups()
    {
        return $this->belongsToMany(
            MasterSkillGroup::class,
            'master_skill_group_skill',
            'master_skill_id',
            'master_skill_group_id'
        );
    }

    // สำหรับ Blade ที่เรียก $skill->skillGroup
    public function skillGroup()
    {
        return $this->skillGroups()->first();
    }
}
