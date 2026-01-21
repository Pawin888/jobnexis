<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumeCertificate extends Model
{
    use HasFactory;

    protected $table = 'resume_certificates';

    protected $fillable = [
        'resume_id',
        'name',
        'issued_by',
        'issued_year',
        'file_path',
    ];

    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }
}
