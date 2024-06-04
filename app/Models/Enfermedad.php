<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enfermedad extends Model
{
    use HasFactory;

    protected $table = 'enfermedades';

    protected $fillable = [
        'nombre',

    ];



    public function empleados()
    {
        return $this->belongsToMany(Empleado::class, 'p_enfermedades_empleados', 'empleado_id', 'enfermedad_id');
    }
}
