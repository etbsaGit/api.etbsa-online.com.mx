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
        'edenred_id'
    ];

    protected $appends = ['nombre_unidad'];

    public function getNombreUnidadAttribute(): string
    {
        $marca = $this->relationLoaded('marca') ? ($this->marca?->nombre ?? '') : ($this->marca_id ? ($this->marca?->nombre ?? '') : '');
        $modelo = $this->relationLoaded('modelo') ? ($this->modelo?->nombre ?? '') : ($this->modelo_id ? ($this->modelo?->nombre ?? '') : '');
        $tipo = $this->relationLoaded('tipo') ? ($this->tipo?->nombre ?? '') : ($this->tipo_id ? ($this->tipo?->nombre ?? '') : '');
        $version = trim((string) ($this->version ?? ''));

        $parts = array_filter([
            trim((string) $marca),
            trim((string) $modelo),
            trim((string) $tipo),
            $version,
        ], fn($val) => $val !== '');

        if (!empty($parts)) {
            return implode(' ', $parts);
        }

        if ($this->relationLoaded('estatus') && $this->estatus?->nombre) {
            return $this->estatus->nombre;
        }

        return 'Unidad';
    }

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('placas', 'LIKE', '%' . $search . '%')
                    ->orWhere('serie', 'LIKE', '%' . $search . '%')
                    ->orWhere('edenred_id', 'LIKE', '%' . $search . '%')
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

        if (!empty($filters['estatus_expediente'])) {
            $estatusExp = $filters['estatus_expediente'];
            $hoy = now()->toDateString();
            $en30Dias = now()->addDays(30)->toDateString();

            if ($estatusExp === 'vencido') {
                $query->whereHas('documentosVigentes', function ($q) use ($hoy) {
                    $q->whereNotNull('fecha_vencimiento')
                        ->whereDate('fecha_vencimiento', '<', $hoy);
                });
            } elseif ($estatusExp === 'por_vencer') {
                $query->whereHas('documentosVigentes', function ($q) use ($hoy, $en30Dias) {
                    $q->whereNotNull('fecha_vencimiento')
                        ->whereDate('fecha_vencimiento', '>=', $hoy)
                        ->whereDate('fecha_vencimiento', '<=', $en30Dias);
                });
            } elseif ($estatusExp === 'incompleto') {
                $query->where(function ($q) {
                    foreach (['seguro', 'tarjeta_circulacion', 'verificacion'] as $tipoDoc) {
                        $q->orWhereDoesntHave('documentosVigentes', function ($qDoc) use ($tipoDoc) {
                            $qDoc->where('tipo_documento', $tipoDoc);
                        });
                    }
                });
            } elseif ($estatusExp === 'al_corriente') {
                foreach (['seguro', 'tarjeta_circulacion', 'verificacion'] as $tipoDoc) {
                    $query->whereHas('documentosVigentes', function ($qDoc) use ($tipoDoc, $en30Dias) {
                        $qDoc->where('tipo_documento', $tipoDoc)
                            ->where(function ($qFecha) use ($en30Dias) {
                                $qFecha->whereNull('fecha_vencimiento')
                                    ->orWhereDate('fecha_vencimiento', '>', $en30Dias);
                            });
                    });
                }
            } elseif ($estatusExp === 'atencion') {
                $query->where(function ($q) use ($en30Dias) {
                    $q->whereHas('documentosVigentes', function ($qDoc) use ($en30Dias) {
                        $qDoc->whereNotNull('fecha_vencimiento')
                            ->whereDate('fecha_vencimiento', '<=', $en30Dias);
                    });
                    foreach (['seguro', 'tarjeta_circulacion', 'verificacion'] as $tipoDoc) {
                        $q->orWhereDoesntHave('documentosVigentes', function ($qDoc) use ($tipoDoc) {
                            $qDoc->where('tipo_documento', $tipoDoc);
                        });
                    }
                });
            }

            unset($filters['estatus_expediente']);
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

    public function documentos()
    {
        return $this->hasMany(VehiclesDocumentos::class, 'vehicle_id')
            ->orderBy('es_vigente', 'desc')
            ->orderBy('fecha_emision', 'desc')
            ->orderBy('id', 'desc');
    }

    public function documentosVigentes()
    {
        return $this->hasMany(VehiclesDocumentos::class, 'vehicle_id')
            ->where('es_vigente', true)
            ->orderBy('id', 'desc');
    }
    public function checklist()
    {
        return $this->hasMany(VehiclesChecklist::class, 'vehicle_id')
            ->orderBy('fecha_revision', 'desc')
            ->orderBy('id', 'desc');
    }
}
