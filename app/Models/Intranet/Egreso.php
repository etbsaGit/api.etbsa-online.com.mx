<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Egreso extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'pago',
        'months',
        'type',
        'entidad',
        'concepto',
        'descripcion',
        'cliente_id',
    ];

    protected $appends = ['total', 'pasivo_corto', 'pasivo_largo'];

    public function getTotalAttribute()
    {
        return $this->pago * $this->months;
    }

    public function getPasivoCortoAttribute()
    {
        $totalDeuda = $this->pago * $this->months;
        return min($this->pago * $this->type, $totalDeuda);
    }

    // 🔹 Cálculo de pasivo largo plazo
    public function getPasivoLargoAttribute()
    {
        $totalDeuda = $this->pago * $this->months;
        $pasivoCorto = min($this->pago * $this->type, $totalDeuda);
        return $totalDeuda - $pasivoCorto;
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
