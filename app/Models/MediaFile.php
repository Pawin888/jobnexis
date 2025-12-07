<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaFile extends Model
{
    protected $table = 'media_files';
    protected $primaryKey = 'mf_id';

    protected $fillable = [
        'mf_m_id', 'mf_path', 'mf_original_name', 'mf_type', 'mf_size'
    ];

    public function media()
    {
        return $this->belongsTo(Media::class, 'mf_m_id', 'm_id');
    }

    // เพิ่ม booted
    protected static function booted()
    {
        static::deleting(function ($file) {
            // ลบไฟล์จริง
            if ($file->mf_path && Storage::exists($file->mf_path)) {
                Storage::delete($file->mf_path);
            }
        });
    }
}