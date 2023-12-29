<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Constelacion extends Model
{
    use HasFactory;

    protected $table = 'constelaciones';

    protected $fillable = [
        'nombre',
    ];

    public function empleados()
    {
        return $this->belongsToMany(Empleado::class, 'p_constelaciones_empleados', 'empleado_id', 'constelacion_id');
    }
}
