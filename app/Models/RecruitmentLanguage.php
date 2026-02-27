<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitmentLanguage extends Model
{
    protected $table = 'recruitment_languages';
    protected $fillable = ['rc_id', 'language', 'proficiency'];

    public function recruitment()
    {
        return $this->belongsTo(Recruitment::class, 'rc_id', 'rc_id');
    }
}