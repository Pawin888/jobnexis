<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomJobGroup extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function roles(): HasMany
    {
        return $this->hasMany(CustomJobRole::class);
    }
}
