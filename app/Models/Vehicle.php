<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'serie',
        'placas',
        'departamento_id',
        'linea_id',
        'sucursal_id',
        'estatus_id',
        'activo',
        'motivo_baja',
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['placas', 'serie']);
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function linea()
    {
        return $this->belongsTo(Linea::class, 'linea_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }

    public function responsable()
    {
        return $this->hasOne(Empleado::class, 'vehicle_id');
    }

    public function empleados()
    {
        return $this->belongsToMany(Empleado::class, 'usuarios_unidades', 'vehicle_id', 'empleado_id')
            ->withTimestamps();
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'vehicle_id');
    }

    public function historialPlacas()
    {
        return $this->hasMany(VehicleHistorialPlacas::class, 'vehicle_id')->orderBy('id', 'desc');
    }

    public function ordenesServicio()
    {
        return $this->hasMany(VehiclesOrdenesServicio::class, 'vehicle_id')
            ->orderBy('fecha_elaboracion', 'desc')
            ->orderBy('id', 'desc');
    }
}
