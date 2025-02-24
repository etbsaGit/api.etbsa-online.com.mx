<?php

namespace App\Models;

use App\Models\Empleado;
use App\Models\Intranet\Sale;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sucursal extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'sucursales';

    protected $fillable = [
        'nombre',
        'direccion',
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre']);
    }

    public function empleado()
    {
        return $this->hasMany(Empleado::class, 'sucursal_id');
    }

    public function linea()
    {
        return $this->belongsToMany(Linea::class, 'p_sucursales_lineas', 'sucursal_id', 'linea_id')->withTimestamps();
    }

    public function bay()
    {
        return $this->hasMany(Bay::class, 'sucursal_id');
    }

    public function post()
    {
        return $this->hasMany(Post::class, 'sucursal_id');
    }

    public function workOrder()
    {
        return $this->hasMany(WorkOrder::class, 'sucursal_id');
    }

    /**
     * Get the travels that start from this sucursal.
     */
    public function startingTravels()
    {
        return $this->hasMany(Travel::class, 'start_point');
    }

    /**
     * Get the travels that end at this sucursal.
     */
    public function endingTravels()
    {
        return $this->hasMany(Travel::class, 'end_point');
    }

    /**
     * Get the sales at this sucursal.
     */
    public function sales()
    {
        return $this->hasMany(Sale::class, 'sucursal_id');
    }

    // ---------------------------------VacationDay---------------------------------------------------------

    public function vacationDays()
    {
        return $this->hasMany(VacationDay::class, 'sucursal_id');
    }

    // ---------------------------------Used---------------------------------------------------------

    public function originUsed()
    {
        return $this->hasMany(Used::class, 'origin_id');
    }

    public function locationUsed()
    {
        return $this->hasMany(Used::class, 'location_id');
    }
}
