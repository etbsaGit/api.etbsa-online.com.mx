<?php

namespace App\Models;

use App\Models\Empleado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Departamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',

        'encargado_id'
    ];

    public function encargado_id(){
        return $this->belongsTo(Empleado::class,'encargado_id');
    }

    public function empleado(){
        return $this->hasMany(Empleado::class,'departamento_id');
    }
}
