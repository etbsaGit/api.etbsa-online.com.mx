<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function analiticaDocs()
    {
        return $this->hasMany(AnaliticaDoc::class, 'analitica_id');
    }
}
