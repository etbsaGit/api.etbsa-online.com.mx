<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Caja\CajaCorte;
use App\Traits\FilterableModel;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Caja\CajaTransaccion;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, FilterableModel, HasRoles, SoftDeletes;

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

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['name', 'email']);
    }

    public function empleado()
    {
        return $this->hasOne(Empleado::class, 'user_id');
    }

    public function survey()
    {
        return $this->hasMany(Survey::class, 'evaluator_id');
    }

    public function answer()
    {
        return $this->hasMany(SurveyAnswer::class, 'evaluee_id');
    }

    public function evaluee()
    {
        return $this->belongsToMany(Survey::class, 'p_survey_evaluee', 'evaluee_id', 'survey_id')->withTimestamps();
    }

    public function grade()
    {
        return $this->hasMany(Grade::class, 'evaluee_id');
    }

    public function post()
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function suggestion()
    {
        return $this->hasMany(Suggestion::class, 'user_id');
    }

    public function vacationCreated()
    {
        return $this->hasMany(VacationDay::class, 'created_by');
    }

    public function vacationValidate()
    {
        return $this->hasMany(VacationDay::class, 'validate_by');
    }

    public function cajaTransaccion()
    {
        return $this->hasMany(CajaTransaccion::class, 'user_id');
    }

    public function cortes()
    {
        return $this->hasMany(CajaCorte::class, 'user_id');
    }
}
