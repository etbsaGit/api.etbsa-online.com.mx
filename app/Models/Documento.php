<?php

namespace App\Models;

use App\Models\Requisito;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'documentos';

    protected $fillable = [
        'nombre',
        'fecha_de_vencimiento',
        'descripcion',

        'requisito_id',
        'expediente_id',
        'estatus_id'
    ];

    public function requisito(){
        return $this->belongsTo(Requisito::class,'requisito_id');
    }

    public function expediente(){
        return $this->belongsTo(Expediente::class,'expediente_id');
    }

    public function estatus(){
        return $this->belongsTo(Estatus::class,'estatus_id');
    }

    public function asignable()
    {
        return $this->morphMany(Archivo::class, 'asignable');
    }
}
