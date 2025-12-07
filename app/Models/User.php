<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\UserProfile;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;



    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'role',
        'is_banned'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    /**
     * Send the email verification notification (Thai version).
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\VerifyEmailThai());
    }
    public function educations()
    {
        return $this->hasMany(Education::class, 'ed_u_id', 'id');
    }
    public function workExperiences()
    {
        return $this->hasMany(WorkExperience::class, 'we_u_id', 'id');
    }
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isEducation()
    {
        return $this->role === 'education';
    }
    public function isProvider()
    {
        return $this->role === 'provider';
    }
    public function isJobber()
    {
        return $this->role === 'jobber';
    }
    public function courseMembers()
    {
        return $this->hasMany(CourseMember::class, 'cm_u_id');
    }
    public function recruitments()
    {
        return $this->hasMany(Recruitment::class, 'rc_u_id');
    }
    public function Certificate()
    {
        return $this->hasMany(Certificate::class, 'cer_u_id');
    }

    public function getStatusLabelAttribute()
    {
        if ($this->is_banned) return 'Banned';
        return $this->email_verified_at ? 'Active' : 'Pending';
    }
    public function courses()
    {
        return $this->hasMany(Course::class, 'c_create_by_id', 'id');
    }
    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'up_u_id', 'id');
    }

    public function companyProfile()
    {
        return $this->hasOne(CompaniesProfile::class, 'co_user_id', 'id');
    }

    public function educationProfile()
    {
        return $this->hasOne(EducationProfile::class, 'e_u_id', 'id');
    }
}
