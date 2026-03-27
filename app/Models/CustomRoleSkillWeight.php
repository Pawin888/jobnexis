<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomRoleSkillWeight extends Model
{
    protected $fillable = [
        'custom_job_role_id',
        'custom_skill_id',
        'taxonomy_level',
        'weight',
    ];
    // ...existing code...

    public function role(): BelongsTo
    {
        return $this->belongsTo(CustomJobRole::class, 'custom_job_role_id');
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(CustomSkill::class, 'custom_skill_id');
    }
}
