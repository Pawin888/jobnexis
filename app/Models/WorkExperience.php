<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkExperience extends Model
{
    use HasFactory;

    protected $table = 'work_experiences';
    protected $primaryKey = 'we_id';

    protected $fillable = [
        'we_company_name',
        'we_start_date',
        'we_end_date',
        'we_u_id',
    ];

    // ความสัมพันธ์กับ User
    public function user()
    {
        return $this->belongsTo(User::class, 'we_u_id');
    }
}
