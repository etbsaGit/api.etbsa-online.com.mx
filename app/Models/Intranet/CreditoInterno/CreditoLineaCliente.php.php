<?php

namespace App\Models\Intranet\CreditoInterno;

use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Intranet\Cliente;
use App\Models\Intranet\CreditoInterno\CreditoSolicitud;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditoLineaCliente extends Model
{
    use HasFactory;

    use FilterableModel;

    protected $table = 'credito_linea_cliente';

    protected $fillable = [
        'cliente_id',
        'linea_id',
        'monto',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
    public function linea()
    {
        return $this->belongsTo(CreditoLineas::class, 'linea_id');
    }
}
