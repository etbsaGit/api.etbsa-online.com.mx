<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferenciaComercial extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'telefono',
        'cliente_id',
        'negocio',
        'domicilio',
        'empresa',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
