<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomJobRole extends Model
{
    protected $fillable = [
        'custom_job_group_id',
        'name',
        'slug',
        'description',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(CustomJobGroup::class, 'custom_job_group_id');
    }

    public function skillWeights(): HasMany
    {
        return $this->hasMany(CustomRoleSkillWeight::class, 'custom_job_role_id');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(
            CustomSkill::class,
            'custom_role_skill_weights',
            'custom_job_role_id',
            'custom_skill_id'
        )->withPivot(['taxonomy_level'])->withTimestamps();
    }
}
