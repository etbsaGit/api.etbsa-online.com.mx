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
    ];


    public function empleado()
    {
        return $this->hasMany(Empleado::class, 'sucursal_id');
    }

    public function linea()
    {
        return $this->belongsToMany(Linea::class, 'p_sucursales_lineas', 'sucursal_id', 'linea_id')->withTimestamps();
    }

    public function bay()
    {
        return $this->hasMany(Bay::class, 'sucursal_id');
    }

    public function post()
    {
        return $this->hasMany(Post::class, 'sucursal_id');
    }

    public function workOrder()
    {
        return $this->hasMany(WorkOrder::class, 'sucursal_id');
    }

     /**
     * Get the travels that start from this sucursal.
     */
    public function startingTravels()
    {
        return $this->hasMany(Travel::class, 'start_point');
    }

    /**
     * Get the travels that end at this sucursal.
     */
    public function endingTravels()
    {
        return $this->hasMany(Travel::class, 'end_point');
    }
}
