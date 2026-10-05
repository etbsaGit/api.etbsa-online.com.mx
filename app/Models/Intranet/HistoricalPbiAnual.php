<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricalPbiAnual extends Model
{
    use HasFactory;

    protected $table = 'historical_pbi_anual';

    protected $guarded = ['id'];

    protected $casts = [
        'anio' => 'integer',
        'venta_maquinaria' => 'float',
        'venta_refacciones' => 'float',
        'venta_servicio' => 'float',
        'venta_riego' => 'float',
        'venta_chevron' => 'float',
        'venta_nuevas_tecnologias' => 'float',
        'total_venta' => 'float',
        'costo_maquinaria' => 'float',
        'costo_refacciones' => 'float',
        'costo_servicio' => 'float',
        'costo_riego' => 'float',
        'costo_chevron' => 'float',
        'costo_nuevas_tecnologias' => 'float',
        'total_costo' => 'float',
        'margen_maquinaria' => 'float',
        'margen_refacciones' => 'float',
        'margen_servicio' => 'float',
        'margen_riego' => 'float',
        'margen_chevron' => 'float',
        'margen_nuevas_tecnologias' => 'float',
        'margen_total' => 'float',
        'pct_margen_maquinaria' => 'float',
        'pct_margen_refacciones' => 'float',
        'pct_margen_servicio' => 'float',
        'pct_margen_riego' => 'float',
        'pct_margen_chevron' => 'float',
        'pct_margen_nuevas_tecnologias' => 'float',
        'pct_margen_total' => 'float',
    ];
}
