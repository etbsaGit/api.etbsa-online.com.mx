<?php

namespace App\Models;

use App\Models\Medicamento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Alergia extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',

        'medicamento_id'
    ];

    public function medicamento(){
        return $this->belongsTo(Medicamento::class,'medicamento_id');
    }

    public function empleados()
    {
        return $this->belongsToMany(Empleado::class, 'p_alergias_empleados', 'empleado_id', 'alergias_id');
    }
}
