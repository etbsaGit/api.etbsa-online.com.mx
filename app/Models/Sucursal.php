<?php

namespace App\Models;

use App\Models\Empleado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';

    protected $fillable = [
        'nombre',
        'direccion',

        'encargado_id'
    ];

    public function encargado_id(){
        return $this->belongsTo(Empleado::class,'encargado_id');
    }

    public function empleado(){
        return $this->hasMany(Empleado::class,'sucursal_id');
    }

    public function lineas()
    {
        return $this->belongsToMany(Linea::class, 'p_sucursales_lineas', 'sucursal_id', 'linea_id');
    }
}
