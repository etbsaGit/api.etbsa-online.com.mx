<?php

namespace App\Models;

use App\Models\Requisito;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Documento extends Pivot
{
    use HasFactory;

    protected $table = 'documentos';

    protected $fillable = [
        'fecha_de_vencimiento',
        'comentario',

        'requisito_id',
        'expediente_id',
        'estatus_id'
    ];

    // protected $appends = ['default_path_folder'];

    public function requisito()
    {
        return $this->belongsTo(Requisito::class, 'requisito_id');
    }

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }

    public function asignable()
    {
        return $this->morphMany(Archivo::class, 'asignable');
    }


    protected function defaultPathFolder(): Attribute
    {
        return Attribute::make(
            get: fn() => "documents/id_" . $this->id . "/requisito_id_" . $this->requisito_id,
        );
    }

}
