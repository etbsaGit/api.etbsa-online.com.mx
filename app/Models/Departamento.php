<?php

namespace App\Models;

use App\Models\Empleado;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Departamento extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'nombre',
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre']);
    }

    public function empleado()
    {
        return $this->hasMany(Empleado::class, 'departamento_id');
    }

    public function post()
    {
        return $this->hasMany(Post::class, 'departamento_id');
    }

    // ---------------------------------VacationDay---------------------------------------------------------

    public function vacationDays()
    {
        return $this->hasMany(VacationDay::class, 'departamento_id');
    }

    // ---------------------------------Vehicle---------------------------------------------------------

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'departamento_id');
    }

    // ---------------------------------Requisiciones---------------------------------------------------------

    public function requisiciones()
    {
        return $this->hasMany(RequisicionPersonal::class, 'departamento_id');
    }

    public function propuestas()
    {
        return $this->hasMany(Propuesta::class, 'departamento_id');
    }
}
