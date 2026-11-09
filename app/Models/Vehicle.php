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
        'marca_id',
        'modelo_id',
        'tipo_id',
        'version',
        'anio',
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('placas', 'LIKE', '%' . $search . '%')
                    ->orWhere('serie', 'LIKE', '%' . $search . '%')
                    ->orWhere('version', 'LIKE', '%' . $search . '%')
                    ->orWhere('anio', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('marca', function ($qMarca) use ($search) {
                        $qMarca->where('nombre', 'LIKE', '%' . $search . '%');
                    })
                    ->orWhereHas('modelo', function ($qModelo) use ($search) {
                        $qModelo->where('nombre', 'LIKE', '%' . $search . '%');
                    })
                    ->orWhereHas('tipo', function ($qTipo) use ($search) {
                        $qTipo->where('nombre', 'LIKE', '%' . $search . '%');
                    });
            });
            unset($filters['search']);
        }

        return $this->scopeFilterSearch($query, $filters);
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

    public function dispersionesGasolina()
    {
        return $this->hasMany(VehiclesDispersionGasolina::class, 'vehicle_id')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');
    }

    public function marca()
    {
        return $this->belongsTo(VehiclesMarcas::class, 'marca_id', 'id');
    }

    public function modelo()
    {
        return $this->belongsTo(VehiclesModelos::class, 'modelo_id', 'id');
    }

    public function tipo()
    {
        return $this->belongsTo(VehiclesTipos::class, 'tipo_id', 'id');
    }
}
