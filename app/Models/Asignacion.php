<?php

namespace App\Models;

use App\Models\TipoDeAsignacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asignacion extends Model
{
    use HasFactory;

    protected $table = 'asignaciones';

    protected $fillable = [
        'nombre',
        'descripcion',

        'tipo_de_asignacion_id',
        'empleado_id'
    ];

    public function tipo_de_asignacion(){
        return $this->belongsTo(TipoDeAsignacion::class,'tipo_de_asignacion_id');
    }

    public function empleado(){
        return $this->belongsTo(Empleado::class,'empleado_id');
    }
}
