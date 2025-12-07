<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $table = 'user_profiles';
    protected $primaryKey = 'up_id';

    protected $fillable = [
        'up_prefix',
        'up_name',
        'up_city',
        'up_birth_date',
        'up_gender',
        'up_phone',
        'up_u_id',
    ];

    // ความสัมพันธ์กับ User
    public function user()
    {
        return $this->belongsTo(User::class, 'up_u_id');
    }
}
