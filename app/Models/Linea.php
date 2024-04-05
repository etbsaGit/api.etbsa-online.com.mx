<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Linea extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
    ];

    public function empleado()
    {
        return $this->hasMany(Empleado::class, 'linea_id');
    }

    public function sucursal()
    {
        return $this->belongsToMany(Sucursal::class, 'p_sucursales_lineas', 'sucursal_id', 'linea_id');
    }

    public function lineaTechnician()
    {
        return $this->hasMany(LineaTechnician::class, 'linea_id');
    }

}
