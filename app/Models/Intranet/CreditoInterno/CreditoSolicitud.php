<?php

namespace App\Models\Intranet\CreditoInterno;

use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Intranet\Cliente;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditoSolicitud extends Model
{
    use HasFactory;

    use FilterableModel;

    protected $table = 'credito_solicitud';

    protected $fillable = [
        'folio',
        'cliente_id',
        'asesor_id',
        'estatus_id',
        'notificado_id',
        'motivo',
        'validated_by',
        'monto_solicitado',
        'linea_id',
        'numero_pagos',
        'notas',
        'anticipo'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
    public function asesor()
    {
        return $this->belongsTo(Empleado::class, 'asesor_id');
    }
    public function notificado()
    {
        return $this->belongsTo(Empleado::class, 'notificado_id');
    }
    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }
    public function validadoPor()
    {
        return $this->belongsTo(Empleado::class, 'validated_by');
    }
    public function linea()
    {
        return $this->belongsTo(CreditoLineas::class, 'linea_id');
    }
    public function pagos()
    {
        return $this->hasMany(CreditoHistorialPagos::class, 'solicitud_id');
    }
    public function getResumenPagosAttribute()
    {
        // si la relacion 'pagos' ya fue cargada con 'with', usamos la colección en memoria
        if ($this->relationLoaded('pagos')) {
            $total = $this->pagos->count();
            if ($total === 0) return '0/0';

            $pagados = $this->pagos->filter(function ($pago) {
                return $pago->estatus && $pago->estatus->nombre === 'Pago Realizado';
            })->count();
            return "{$pagados}/{$total}";
        }
        // si no está cargada en memoria, consultamos en BD
        $total = $this->pagos()->count();
        if ($total === 0) return '0/0';
        $pagados = $this->pagos()->whereHas('estatus', function ($query) {
            $query->where('nombre', 'Pago Realizado');
        })->count();

        return "{$pagados}/{total}";
    }
    public function getProximoPagoAttribute()
    {
        $estatusNoPagados = ['Pago Pendiente', 'Pago Atrasado'];
        // si la relación pagos ya está en memoria por el with
        if ($this->relationLoaded('pagos')) {
            $proximo = $this->pagos->filter(function ($pago) use ($estatusNoPagados) {
                return $pago->estatus && in_array($pago->estatus->nombre, $estatusNoPagados);
            })->sortBy('fecha_a_pagar')->first();

            return $proximo ? $proximo->fecha_a_pagar : null;
        }

        // si no está en la memoria
        $proximo = $this->pagos()
            ->whereHas('estatus', function ($query) use ($estatusNoPagados) {
                $query->whereIn('nombre', $estatusNoPagados);
            })
            ->orderBy('fecha_a_pagar', 'asc')
            ->first();
        return $proximo ? $proximo->fecha_a_pagar : null;
    }
    public function historial()
    {
        return $this->hasMany(CreditoHistorical::class, 'solicitud_id');
    }
    public function archivos()
    {
        return $this->hasMany(CreditoDocs::class, 'solicitud_id');
    }
}
