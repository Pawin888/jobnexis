<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MasterSkillGroup extends Model
{
    protected $table = 'master_skill_groups';

    protected $fillable = [
        'esco_uri',
        'esco_code',
        'name',
        'description',
        'parent_id',
        'source',
        'is_active',
    ];

    /**
     * Group → Skills
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(
            MasterSkill::class,
            'master_skill_group_skill',
            'master_skill_group_id',
            'master_skill_id'
        );
    }
}
