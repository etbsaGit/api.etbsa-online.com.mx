<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditoRelacion extends Model
{
    use HasFactory;

    protected $fillable = [
        'credito_declaracion_id',
        'credito_concepto_id',
        'valor',
        'nombre'
    ];

    public function declaracion()
    {
        return $this->belongsTo(CreditoDeclaracion::class, 'credito_declaracion_id');
    }

    public function concepto()
    {
        return $this->belongsTo(CreditoConcepto::class, 'credito_concepto_id');
    }
}
