<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehiclesDispersionGasolina extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'vehicles_dispersion_gasolina';

    protected $fillable = [
        'vehicle_id',
        'solicitado_por',
        'registrado_por',
        'suc_cargo_id',
        'depto_cargo_id',
        'lts_solicitados',
        'lts_dispersados',
        'km_evento',
        'foto_km_url',
        'fecha_requerida',
        'recorrido_url',
        'notas',
        'estatus_id',
        'is_automatica',
    ];

    protected $casts = [
        'is_automatica' => 'boolean',
        'lts_solicitados' => 'integer',
        'lts_dispersados' => 'integer',
        'km_evento' => 'integer',
        'fecha_requerida' => 'datetime',
    ];

    // -Relations-
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function solicitante()
    {
        return $this->belongsTo(Empleado::class, 'solicitado_por');
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function sucursalCargo()
    {
        return $this->belongsTo(VehiclesSucCargoA::class, 'suc_cargo_id');
    }

    public function departamentoCargo()
    {
        return $this->belongsTo(VehiclesDeptoCargoA::class, 'depto_cargo_id');
    }

    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }

    public function bitacora()
    {
        return $this->hasMany(VehiclesGasolinaBitacora::class, 'dispersion_id')
            ->with('user.empleado')
            ->with('estatus')
            ->orderBy('created_at', 'desc');
    }
    public function notificados()
    {
        return $this->hasMany(VehiclesDispersionNotificados::class, 'dispersion_id')
            ->with('notificado');
    }

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%");
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

        $solicitadoPor = $filters['solicitado_por'] ?? $filters['solicitante_id'] ?? null;
        if (!empty($solicitadoPor)) {
            $query->where('solicitado_por', $solicitadoPor);
        }

        if (!empty($filters['suc_cargo_id'])) {
            $query->where('suc_cargo_id', $filters['suc_cargo_id']);
        }

        if (!empty($filters['depto_cargo_id'])) {
            $query->where('depto_cargo_id', $filters['depto_cargo_id']);
        }

        if (!empty($filters['estatus_id'])) {
            if (is_array($filters['estatus_id'])) {
                $query->whereIn('estatus_id', $filters['estatus_id']);
            } else {
                $query->where('estatus_id', $filters['estatus_id']);
            }
        }

        if (!empty($filters['fecha_desde'])) {
            $query->whereDate('created_at', '>=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $query->whereDate('created_at', '<=', $filters['fecha_hasta']);
        }

        return $query;
    }
}
