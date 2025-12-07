<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;
    protected $table = 'media';
    protected $primaryKey = 'm_id';
    protected $fillable = [
        'm_name',
        'm_path',
        'm_index',
        'm_l_id', // FK to lessons
        'm_desc',
        'm_c_id',
    ];
    public function lesson()
    {
        return $this->belongsTo(\App\Models\Lesson::class, 'm_l_id', 'l_id');
    }
    public function files()
    {
    return $this->hasMany(MediaFile::class, 'mf_m_id', 'm_id');
    }
}
