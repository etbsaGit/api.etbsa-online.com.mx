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
        'estatus_id',
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

    public function detalles()
    {
        return $this->hasMany(VehiclesDetallesServicio::class, 'orden_servicio_id');
    }

    public function detallesServicio()
    {
        return $this->hasMany(VehiclesDetallesServicio::class, 'orden_servicio_id');
    }

    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }

    public function bitacora()
    {
        return $this->hasMany(VehiclesOrdenesBitacora::class, 'orden_servicio_id')
            ->with('user.empleado')
            ->with('estatus')
            ->orderBy('created_at', 'desc');
    }

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('autorizacion_compras', 'LIKE', "%{$search}%")
                    ->orWhere('orden_compra', 'LIKE', "%{$search}%")
                    ->orWhereHas('vehicle', function (Builder $vq) use ($search) {
                        $vq->where('placas', 'LIKE', "%{$search}%")
                            ->orWhere('serie', 'LIKE', "%{$search}%")
                            ->orWhereHas('historialPlacas', function (Builder $hq) use ($search) {
                                $hq->where('placa', 'LIKE', "%{$search}%");
                            });
                    })
                    ->orWhereHas('proveedor', function (Builder $pq) use ($search) {
                        $pq->where('nombre', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('solicitante', function (Builder $sq) use ($search) {
                        $sq->where('nombre', 'LIKE', "%{$search}%")
                            ->orWhere('apellido_paterno', 'LIKE', "%{$search}%")
                            ->orWhere('apellido_materno', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('detalles', function (Builder $dq) use ($search) {
                        $dq->where('descripcion', 'LIKE', "%{$search}%")
                            ->orWhere('km_evento', 'LIKE', "%{$search}%")
                            ->orWhereHas('servicio', function (Builder $scq) use ($search) {
                                $scq->where('nombre', 'LIKE', "%{$search}%")
                                    ->orWhereHas('tipoServicio', function (Builder $tsq) use ($search) {
                                        $tsq->where('nombre', 'LIKE', "%{$search}%");
                                    });
                            });
                    });
            });
        }

        if (!empty($filters['vehicle_ids']) && is_array($filters['vehicle_ids'])) {
            $query->whereIn('vehicle_id', $filters['vehicle_ids']);
        }

        if (!empty($filters['vehicle_id'])) {
            if (is_array($filters['vehicle_id'])) {
                $query->whereIn('vehicle_id', $filters['vehicle_id']);
            } else {
                $query->where('vehicle_id', $filters['vehicle_id']);
            }
        }

        if (!empty($filters['proveedor_id'])) {
            $query->where('proveedor_id', $filters['proveedor_id']);
        }

        if (!empty($filters['solicitante_id'])) {
            $query->where('solicitante_id', $filters['solicitante_id']);
        }

        if (!empty($filters['suc_cargo_id'])) {
            $query->where('suc_cargo_id', $filters['suc_cargo_id']);
        }

        if (!empty($filters['depto_cargo_id'])) {
            $query->where('depto_cargo_id', $filters['depto_cargo_id']);
        }

        if (!empty($filters['servicio_id'])) {
            $query->whereHas('detalles', function (Builder $dq) use ($filters) {
                $dq->where('servicio_id', $filters['servicio_id']);
            });
        }

        if (!empty($filters['tipo_servicio_id'])) {
            $query->whereHas('detalles.servicio', function (Builder $sq) use ($filters) {
                $sq->where('tipo_servicio_id', $filters['tipo_servicio_id']);
            });
        }

        if (!empty($filters['fecha_desde'])) {
            $query->whereDate('fecha_elaboracion', '>=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $query->whereDate('fecha_elaboracion', '<=', $filters['fecha_hasta']);
        }

        return $query;
    }
}
