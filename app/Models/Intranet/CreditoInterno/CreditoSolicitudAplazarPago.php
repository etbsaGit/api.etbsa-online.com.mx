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
    public function scopeFilter($query, $filters) {}
}
