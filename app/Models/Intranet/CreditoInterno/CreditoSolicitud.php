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
}
