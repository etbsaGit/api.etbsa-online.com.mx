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

        'documento_que_avala_id',
        'estado_del_estudio_id',
        'escuela_id',
        'escolaridad_id',
        'empleado_id'
    ];

    public function documento_que_avala(){
        return $this->belongsTo(DocumentoQueAvala::class,'documento_que_avala_id');
    }

    public function estado_del_estudio(){
        return $this->belongsTo(EstadoDeEstudio::class,'estado_del_estudio_id');
    }

    public function escuela(){
        return $this->belongsTo(Escuela::class,'escuela_id');
    }

    public function escolaridad(){
        return $this->belongsTo(Escolaridad::class,'escolaridad_id');
    }

    public function empleado(){
        return $this->belongsTo(Empleado::class,'empleado_id');
    }

}
