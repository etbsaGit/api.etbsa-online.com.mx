<?php

namespace App\Models\Intranet;

use App\Models\Estatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Finca extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'valor',
        'costo',
        'estatus_id',
        'cliente_id',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }
}
