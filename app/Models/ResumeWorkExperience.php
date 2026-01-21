<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumeWorkExperience extends Model
{
    use HasFactory;

    protected $table = 'resume_work_experiences';

    protected $fillable = [
        'resume_id',
        'job_title',
        'company_name',
        'start_date',
        'end_date',
        'is_current',
        'description',
    ];

    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }
}
