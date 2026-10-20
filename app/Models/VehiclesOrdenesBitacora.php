<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehiclesOrdenesBitacora extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'vehicles_ordenes_bitacora';

    protected $fillable = [
        'orden_servicio_id',
        'estatus_id',
        'user_id',
        'comentarios',
    ];

    // -Relations-
    public function ordenServicio()
    {
        return $this->belongsTo(VehiclesOrdenesServicio::class, 'orden_servicio_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->orWhereHas('ordenServicio', function (Builder $vq) use ($search) {
                    $vq->where('placas', 'LIKE', "%{$search}%")
                        ->orWhere('serie', 'LIKE', "%{$search}%")
                        ->orWhereHas('historialPlacas', function (Builder $hq) use ($search) {
                            $hq->where('placa', 'LIKE', "%{$search}%");
                        });
                })
                    ->orWhereHas('user', function (Builder $ep) use ($search) {
                        $ep->where('name', 'LIKE', "%{ $search}%");
                    })
                    ->orWhereHas('ordenServicio.solicitante', function (Builder $sq) use ($search) {
                        $sq->where('nombre', 'LIKE', "%{$search}%")
                            ->orWhere('apellido_paterno', 'LIKE', "%{$search}%")
                            ->orWhere('apellido_materno', 'LIKE', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['orden_servicio_id'])) {
            $query->where('orden_servicio_id', $filters['orden_servicio_id']);
        }

        if (!empty($filters['estatus_id'])) {
            $query->where('estatus_id', $filters['estatus_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
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
