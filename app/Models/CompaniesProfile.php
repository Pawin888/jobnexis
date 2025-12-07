<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class CompaniesProfile extends Model
{
    protected $table = 'companies_profiles';
    protected $primaryKey = 'co_id';
    public $timestamps = true;

    protected $fillable = [
        'co_user_id',
        'co_name','co_email','co_phone','co_birthday','co_type',
        'co_number','co_jobber_amount','co_address','co_province','co_details',
        'co_profile_img','co_banner_img',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'co_user_id');
    }
     protected function coBannerImg(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['co_banner_img'] ?? null,
            set: fn ($value) => ['co_banner_img' => $value],
        );
    }
}

