<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Linea extends Model
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
        return $this->hasMany(Empleado::class,'linea_id');
    }

    public function sucursal()
    {
        return $this->belongsToMany(Sucursal::class, 'p_sucursales_lineas', 'sucursal_id', 'linea_id');
    }
}
