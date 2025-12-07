<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recruitment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'recruitments';
    protected $primaryKey = 'rc_id';

    protected $fillable = [
        'rc_title',
        'rc_description',
        'rc_requirements',
        'rc_salary',
        'rc_location_text',
        'rc_location_link',
        'rc_type',
        'rc_status',
        'rc_work_mode',
        'rc_posted_at',
        'rc_expire_at',
        'rc_application_url',
        'rc_views',
        'rc_u_id',
    ];

    protected $casts = [
        'rc_posted_at' => 'datetime',
        'rc_expire_at' => 'date',
    ];

    /** เจ้าของประกาศ (User) */
    public function owner()
    {
        return $this->belongsTo(\App\Models\User::class, 'rc_u_id');
    }

    /** Scope: ของ user คนนี้ */
    public function scopeOwnedBy($q, $userId)
    {
        return $q->where('rc_u_id', $userId);
    }

    /** Scope: ยังเปิดอยู่และไม่หมดอายุ */
    public function scopeOpen($q)
    {
        return $q->where('rc_status', 'open')
                 ->where(function($qq){
                     $qq->whereNull('rc_expire_at')
                        ->orWhereDate('rc_expire_at', '>=', now()->toDateString());
                 });
    }
}
