<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehiclesChecklist extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'vehicles_checklist';

    protected $fillable = [
        'vehicle_id',
        'verificador_id',
        'fecha_revision',
        'notas',
        'trimestre_revision'
    ];

    public function scopeFilter(Builder $query, array $filters)
    {
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('notas', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('vehicle', function ($qVeh) use ($search) {
                        $qVeh->where('placas', 'LIKE', '%' . $search . '%')
                            ->orWhere('serie', 'LIKE', '%' . $search . '%')
                            ->orWhere('version', 'LIKE', '%' . $search . '%')
                            ->orWhereHas('marca', fn($qm) => $qm->where('nombre', 'LIKE', '%' . $search . '%'))
                            ->orWhereHas('modelo', fn($qmo) => $qmo->where('nombre', 'LIKE', '%' . $search . '%'));
                    })
                    ->orWhereHas('verificador', function ($qUser) use ($search) {
                        $qUser->where('name', 'LIKE', '%' . $search . '%')
                            ->orWhere('email', 'LIKE', '%' . $search . '%')
                            ->orWhereHas('empleado', function ($qEmp) use ($search) {
                                $qEmp->where('nombre', 'LIKE', '%' . $search . '%')
                                    ->orWhere('segundo_nombre', 'LIKE', '%' . $search . '%')
                                    ->orWhere('apellido_paterno', 'LIKE', '%' . $search . '%')
                                    ->orWhere('apellido_materno', 'LIKE', '%' . $search . '%');
                            });
                    });
            });
            unset($filters['search']);
        }

        if (!empty($filters['anio'])) {
            $query->whereYear('fecha_revision', (int) $filters['anio']);
            unset($filters['anio']);
        }

        if (!empty($filters['sucursal_id'])) {
            $sucId = (int) $filters['sucursal_id'];
            $query->whereHas('vehicle', fn($qv) => $qv->where('sucursal_id', $sucId));
            unset($filters['sucursal_id']);
        }

        if (!empty($filters['vehicle_id'])) {
            $query->where('vehicle_id', (int) $filters['vehicle_id']);
            unset($filters['vehicle_id']);
        }

        if (!empty($filters['trimestre_revision'])) {
            $query->where('trimestre_revision', (int) $filters['trimestre_revision']);
            unset($filters['trimestre_revision']);
        }

        if (!empty($filters['verificador_id'])) {
            $query->where('verificador_id', (int) $filters['verificador_id']);
            unset($filters['verificador_id']);
        }

        if (!empty($filters['estatus_checklist'])) {
            $est = $filters['estatus_checklist'];
            if ($est === 'completo') {
                $query->whereDoesntHave('detalles', fn($qd) => $qd->where('existencia', false))
                    ->whereHas('detalles');
            } elseif ($est === 'incompleto') {
                $query->whereHas('detalles', fn($qd) => $qd->where('existencia', false));
            }
            unset($filters['estatus_checklist']);
        }

        unset($filters['per_page'], $filters['page'], $filters['sin_revisar_trimestre']);

        return $this->scopeFilterSearch($query, $filters);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
    public function verificador()
    {
        return $this->belongsTo(User::class, 'verificador_id');
    }
    public function detalles()
    {
        return $this->hasMany(VehiclesChecklistDetalle::class, 'checklist_id');
    }
}
