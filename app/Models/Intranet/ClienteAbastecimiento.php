<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteAbastecimiento extends Model
{
    use HasFactory;

    protected $table = 'clientes_abastecimientos';

    protected $fillable = [
        'cantidad',
        'cliente_id',
        'abastecimiento_id',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function abastecimiento()
    {
        return $this->belongsTo(Abastecimiento::class, 'abastecimiento_id');
    }
}
