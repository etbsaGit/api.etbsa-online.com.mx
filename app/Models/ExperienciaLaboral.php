<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExperienciaLaboral extends Model
{
    use HasFactory;

    protected $table = 'experiencias_laborales';

    protected $fillable = [
        'lugar',
        'inicio',
        'termino',
        'telefono',

        'puesto_id',
        'direccion',
        'empleado_id',
    ];

    public function puesto_id(){
        return $this->belongsTo(Puesto::class,'puesto_id');
    }


    public function empleado_id(){
        return $this->belongsTo(Empleado::class,'empleado_id');
    }

}
