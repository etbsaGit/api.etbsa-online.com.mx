<?php

namespace App\Models\Intranet;

use App\Models\Empleado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Analitica extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'efectivo',
        'caja',
        'gastos',
        'documentospc',
        'mercancias',
        'status',
        'fecha',
        'comentarios',
        'cliente_id',
        'empleado_id'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function analiticaDocs()
    {
        return $this->hasMany(AnaliticaDoc::class, 'analitica_id');
    }
}
