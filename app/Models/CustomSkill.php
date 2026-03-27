<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomSkill extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function roleWeights(): HasMany
    {
        return $this->hasMany(CustomRoleSkillWeight::class, 'custom_skill_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            CustomJobRole::class,
            'custom_role_skill_weights',
            'custom_skill_id',
            'custom_job_role_id'
        )->withPivot(['taxonomy_level'])->withTimestamps();
    }
}
