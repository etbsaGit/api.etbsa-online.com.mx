<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricalPbiVenta extends Model
{
    use HasFactory;

    protected $table = 'historical_pbi_ventas';

    protected $guarded = ['id'];

    protected $casts = [
        'precio_venta' => 'float',
        'total_costo' => 'float',
        'margen' => 'float',
        'margen_pct' => 'float',
        'total' => 'float',
        'mes_no' => 'integer',
        'anio' => 'integer',
    ];
}
