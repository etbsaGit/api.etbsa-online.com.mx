<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteCultivo extends Model
{
    use HasFactory;

    protected $table = 'clientes_cultivos';

    protected $fillable = [
        'cliente_id',
        'cultivo_id',
        'tipo_cultivo_id'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function cultivo()
    {
        return $this->belongsTo(Cultivo::class, 'cultivo_id');
    }

    public function tipoCultivo()
    {
        return $this->belongsTo(TipoCultivo::class, 'tipo_cultivo_id');
    }
}
