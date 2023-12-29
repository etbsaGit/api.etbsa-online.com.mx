<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferenciaPersonal extends Model
{
    use HasFactory;

    protected $table = 'referencias_personales';

    protected $fillable = [
        'nombre',
        'telefono',
        'parentesco',

        'direccion',
        'empleado_id'
    ];


    public function empleado_id(){
        return $this->belongsTo(Empleado::class,'empleado_id');
    }
}
