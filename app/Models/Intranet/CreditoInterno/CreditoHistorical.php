<?php

namespace App\Models\Intranet\CreditoInterno;

use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Intranet\Cliente;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditoHistorical extends Model
{
    use HasFactory;

    use FilterableModel;

    protected $table = 'credito_historical';

    protected $fillable = [
        'solicitud_id',
        'descripcion',
        'estatus_id',
        'empleado_id'
    ];

    public function solicitud()
    {
        return $this->belongsTo(CreditoSolicitud::class, 'solicitud_id');
    }
    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }
}
