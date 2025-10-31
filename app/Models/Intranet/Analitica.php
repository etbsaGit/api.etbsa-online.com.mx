<?php

namespace App\Models\Intranet;

use App\Models\Empleado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
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

    protected static function booted()
    {
        static::deleting(function ($analitica) {
            foreach ($analitica->analiticaDocs as $doc) {
                if ($doc->ruta_archivo && Storage::disk('s3')->exists($doc->ruta_archivo)) {
                    Storage::disk('s3')->delete($doc->ruta_archivo);
                }
                $doc->delete();
            }
        });
    }

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
