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

class CreditoSolicitudVoBoCredito extends Model
{
    use HasFactory;

    use FilterableModel;

    protected $table = 'credito_solicitud_vobo_credito';

    protected $fillable = [
        'solicitud_id',
        'empleado_id',
        'estatus_id',
        'notas'
    ];

    public function solicitud()
    {
        return $this->belongsTo(CreditoSolicitud::class, 'solicitud_id');
    }
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }
    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }
}
