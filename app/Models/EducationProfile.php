<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationProfile extends Model
{
    use HasFactory;


    protected $table = 'education_profiles';
    protected $primaryKey = 'e_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'e_name',
        'e_phone',
        'e_email',
        'e_website',
        'e_birthday',
        'e_number',
        'e_address',
        'e_province',
        'e_detail',
        'e_u_id',
    ];


    protected $casts = [
        'e_birthday' => 'date',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'e_u_id');
    }
}
