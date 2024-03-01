<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function empleado(){
        return $this->hasOne(Empleado::class,'user_id');
    }

    public function survey(){
        return $this->hasMany(Survey::class,'evaluator_id');
    }

    public function answer(){
        return $this->hasMany(SurveyAnswer::class,'evaluee_id');
    }

    public function evaluee()
    {
        return $this->belongsToMany(Survey::class, 'p_survey_evaluee', 'evaluee_id', 'survey_id')->withTimestamps();
    }

    public function grade(){
        return $this->hasMany(Grade::class,'evaluee_id');
    }

}
