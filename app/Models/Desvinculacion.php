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
        'tipoDeDesvinculacion_id'
    ];

    public function tipoDeDesvinculacion(){
        return $this->belongsTo(TipoDeDesvinculacion::class,'tipoDeDesvinculacion_id');
    }


    public function empleado(){
        return $this->hasOne(Empleado::class,'desvinculacion_id');
    }
}
