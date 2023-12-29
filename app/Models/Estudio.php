<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudio extends Model
{
    use HasFactory;

    protected $fillable = [
        'inicio',
        'termino',

        'documentoQueAvala_id',
        'estadoDelEstudio_id',
        'escuela_id',
        'escolaridad_id',
        'empleado_id'
    ];

    public function documentoQueAvala_id(){
        return $this->belongsTo(DocumentoQueAvala::class,'documentoQueAvala_id');
    }

    public function estadoDelEstudio_id(){
        return $this->belongsTo(EstadoDeEstudio::class,'estadoDelEstudio_id');
    }

    public function escuela_id(){
        return $this->belongsTo(Escuela::class,'escuela_id');
    }

    public function escolaridad_id(){
        return $this->belongsTo(Escolaridad::class,'escolaridad_id');
    }

    public function empleado_id(){
        return $this->belongsTo(Empleado::class,'empleado_id');
    }

}
