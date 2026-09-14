<?php

namespace App\Models\Intranet\CreditoInterno;

use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Intranet\Cliente;
use App\Models\Sucursal;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditoSolicitudAplazarPago extends Model
{
    use HasFactory;

    use FilterableModel;

    protected $table = 'credito_solicitud_aplazar_pago';

    protected $fillable = [
        'pago_id',
        'solicitante_id',
        'estatus_id',
        'validated_by',
        'fecha_actual',
        'fecha_nueva',
        'motivo'
    ];

    public function pago()
    {
        return $this->belongsTo(CreditoHistorialPagos::class, 'pago_id');
    }
    public function solicitante()
    {
        return $this->belongsTo(Empleado::class, 'solicitante_id');
    }
    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }
    public function validadoPor()
    {
        return $this->belongsTo(Empleado::class, 'validated_by');
    }
    public function scopeFilter($query, $filters)
    {
        if (isset($filters['estatus_id']) && !empty($filters['estatus_id'])) {
            $query->where('estatus_id', $filters['estatus_id']);
        }
        if (isset($filters['solicitante_id']) && !empty($filters['solicitante_id'])) {
            $query->where('solicitante_id', $filters['solicitante_id']);
        }
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('motivo', 'LIKE', "%{$search}%")
                    ->orWhereHas('pago.solicitud', function ($sub) use ($search) {
                        $sub->where('folio', 'LIKE', "%{$search}%")
                            ->orWhereHas('cliente', function ($c) use ($search) {
                                $c->where('nombre', 'LIKE', "%{$search}%")
                                    ->orWhere('rfc', 'LIKE', "%{$search}%");
                            });
                    });
            });
        }
        if (isset($filters['cliente_id']) && !empty($filters['cliente_id'])) {
            $query->whereHas('pago.solicitud', function ($q) use ($filters) {
                $q->where('cliente_id', $filters['cliente_id']);
            });
        }
        if (isset($filters['sucursal_id']) && !empty($filters['sucursal_id'])) {
            $query->whereHas('pago.solicitud', function ($q) use ($filters) {
                $q->where('sucursal_id', $filters['sucursal_id']);
            });
        }
        if (isset($filters['asesor_id']) && !empty($filters['asesor_id'])) {
            $query->whereHas('pago.solicitud', function ($q) use ($filters) {
                $q->where('asesor_id', $filters['asesor_id']);
            });
        }
    }
}
