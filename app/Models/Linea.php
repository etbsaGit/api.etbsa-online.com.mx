<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Linea extends Model
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
        return $this->hasMany(Empleado::class, 'linea_id');
    }

    public function sucursal()
    {
        return $this->belongsToMany(Sucursal::class, 'p_sucursales_lineas', 'sucursal_id', 'linea_id');
    }

    public function lineaTechnician()
    {
        return $this->hasMany(LineaTechnician::class, 'linea_id');
    }

    public function bay()
    {
        return $this->hasMany(Bay::class, 'linea_id');
    }

    public function post()
    {
        return $this->hasMany(Post::class, 'linea_id');
    }

    public function workOrder()
    {
        return $this->hasMany(WorkOrder::class, 'linea_id');
    }

    public function useds()
    {
        return $this->hasMany(Used::class, 'linea_id');
    }
}
