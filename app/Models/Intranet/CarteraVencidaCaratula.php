<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarteraVencidaCaratula extends Model
{
    use HasFactory;

    protected $table = 'cartera_vencida_caratula';

    protected $fillable = [
        'id_cliente',
        'cliente',
        'depto',
        'folio',
        'fecha_vencimiento',
        'id_sucursal',
        'sucursal',
        'linea',
        'concepto',
        'serie_fiscal',
        'mas_de_90',
        'mas_de_60',
        'mas_de_30',
        'mas_de_15',
        'de_1_a_15',
        'por_vencer',
        'total',
    ];

    protected $casts = [
        'mas_de_90' => 'float',
        'mas_de_60' => 'float',
        'mas_de_30' => 'float',
        'mas_de_15' => 'float',
        'de_1_a_15' => 'float',
        'por_vencer' => 'float',
        'total' => 'float',
    ];
}
