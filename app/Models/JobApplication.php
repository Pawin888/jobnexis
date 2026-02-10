<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'recruitment_id',
        'jobber_id',
        'resume_id',
        'status',
        'cover_letter',
        'applied_at',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    // Relationships
    public function recruitment()
    {
        return $this->belongsTo(Recruitment::class, 'recruitment_id', 'rc_id');
    }

    public function jobber()
    {
        return $this->belongsTo(User::class, 'jobber_id');
    }

    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }

    // Scopes
    public function scopeByRecruit($query, $recruitmentId)
    {
        return $query->where('recruitment_id', $recruitmentId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('applied_at');
    }
}