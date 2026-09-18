<?php

namespace App\Models\Intranet\CreditoInterno;

use App\Models\Empleado;
use App\Models\Estatus;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditoSolicitudVoBoGerencia extends Model
{
    use HasFactory;

    use FilterableModel;

    protected $table = 'credito_solicitud_vobo_gerencia';

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
