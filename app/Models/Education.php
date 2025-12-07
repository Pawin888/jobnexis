<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    use HasFactory;

    protected $table = 'educations';
    protected $primaryKey = 'ed_id';

    protected $fillable = [
        'ed_name',
        'ed_start_date',
        'ed_end_date',
        'ed_u_id',
    ];

    // ความสัมพันธ์กับ User
    public function user()
    {
        return $this->belongsTo(User::class, 'ed_u_id');
    }
}
