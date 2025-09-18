<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GanaderaInversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'ciclo',
        'unidades',
        'costo',
        'cantidad',
        'precio',
        'ganado_id',
        'cliente_id',
    ];

    protected $appends = ['total', 'ingreso', 'utilidad'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function ganado()
    {
        return $this->belongsTo(Ganado::class, 'ganado_id');
    }

    public function getTotalAttribute()
    {
        return $this->unidades * $this->costo;
    }

    public function getIngresoAttribute()
    {
        return $this->cantidad * $this->precio;
    }

    public function getUtilidadAttribute()
    {
        return $this->ingreso - $this->total;
    }
}
