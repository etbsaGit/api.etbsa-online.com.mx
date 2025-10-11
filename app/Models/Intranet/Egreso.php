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
        'entidad',
        'concepto',
        'descripcion',
        'cliente_id',
    ];

    protected $appends = ['total'];

    public function getTotalAttribute()
    {
        return $this->pago * $this->months;
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
