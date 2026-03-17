<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Resume;
use App\Models\Recruitment;

class ProviderCandidateInvite extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'resume_id',
        'recruitment_id',
        'message',
        'invited_at',
    ];

    protected $casts = [
        'invited_at' => 'datetime',
    ];

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function resume()
    {
        return $this->belongsTo(Resume::class, 'resume_id');
    }

    public function recruitment()
    {
        return $this->belongsTo(Recruitment::class, 'recruitment_id', 'rc_id');
    }
}
