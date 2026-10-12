<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarteraVencidaIngreso extends Model
{
    use HasFactory;

    protected $table = 'cartera_vencida_ingresos';

    protected $fillable = [
        'id_cliente',
        'v_rec_no',
        'folio',
        'fecha_movimiento',
        'ano',
        'mes',
        'dia',
        'fecha_orden',
        'descripcion',
        'credito',
        'contado',
        'nota_debito',
        'nota_credito',
        'pagos_cobranza',
        'pagos_mostrador',
        'sucursal',
        'condiciones',
        'tipo_moneda',
    ];

    protected $casts = [
        'ano' => 'integer',
        'dia' => 'integer',
        'credito' => 'float',
        'contado' => 'float',
        'nota_debito' => 'float',
        'nota_credito' => 'float',
        'pagos_cobranza' => 'float',
        'pagos_mostrador' => 'float',
    ];
}
