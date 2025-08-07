<?php

namespace App\Models\Caja;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CajaDetalleEfectivo extends Model
{
    use HasFactory;

    protected $fillable = [
        'cantidad',
        'denominacion_id',
        'corte_id'
    ];

    public function denominacion()
    {
        return $this->belongsTo(CajaDenominacion::class, 'denominacion_id');
    }

    public function corte()
    {
        return $this->belongsTo(CajaCorte::class, 'corte_id');
    }
}
