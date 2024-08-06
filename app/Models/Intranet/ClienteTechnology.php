<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteTechnology extends Model
{
    use HasFactory;

    protected $table = 'clientes_technologies';

    protected $fillable = [
        'cantidad',
        'hectareas',
        'cliente_id',
        'nueva_tecnologia_id',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function nuevaTecnologia()
    {
        return $this->belongsTo(NuevaTecnologia::class, 'nueva_tecnologia_id');
    }
}
