<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehiclesOrdenesServicio extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'vehicles_ordenes_servicio';

    protected $fillable = [
        'autorizacion_compras',
        'orden_compra',
        'fecha_elaboracion',
        'vehicle_id',
        'proveedor_id',
        'solicitante_id',
        'suc_cargo_id',
        'depto_cargo_id',
        'factura',
        'factura_url',
        'orden_compra_url'
    ];

    // -Relations-
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(VehiclesServicioProveedores::class, 'proveedor_id');
    }

    public function solicitante()
    {
        return $this->belongsTo(Empleado::class, 'solicitante_id');
    }

    public function sucursalCargo()
    {
        return $this->belongsTo(VehiclesSucCargoA::class, 'suc_cargo_id');
    }

    public function departamentoCargo()
    {
        return $this->belongsTo(VehiclesDeptoCargoA::class, 'depto_cargo_id');
    }

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre']);
    }
}
