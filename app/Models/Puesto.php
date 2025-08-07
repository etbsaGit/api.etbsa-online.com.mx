<?php

namespace App\Models;

use App\Models\Empleado;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Puesto extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'nombre'
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre']);
    }

    public function empleado()
    {
        return $this->hasMany(Empleado::class, 'puesto_id');
    }

    public function survey()
    {
        return $this->hasMany(Survey::class, 'puesto_id');
    }

    public function skill()
    {
        return $this->belongsToMany(Skill::class, 'p_skills_puestos', 'puesto_id', 'skill_id');
    }

    public function post()
    {
        return $this->hasMany(Post::class, 'puesto_id');
    }

    // ---------------------------------VacationDay---------------------------------------------------------

    public function vacationDays()
    {
        return $this->hasMany(VacationDay::class, 'puesto_id');
    }

    // ---------------------------------Requisiciones---------------------------------------------------------

    public function requisiciones()
    {
        return $this->hasMany(RequisicionPersonal::class, 'puesto_id');
    }
}
