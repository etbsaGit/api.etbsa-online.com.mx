<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Desvinculacion extends Model
{
    use HasFactory;

    protected $table = 'desvinculaciones';

    protected $fillable = [
        'fecha',
        'comentarios',
        'tipo_de_desvinculacion_id'
    ];

    public function tipo_de_desvinculacion(){
        return $this->belongsTo(TipoDeDesvinculacion::class,'tipo_de_desvinculacion_id');
    }


    public function empleado(){
        return $this->hasOne(Empleado::class,'desvinculacion_id');
    }
}
