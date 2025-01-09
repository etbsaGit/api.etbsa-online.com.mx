<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Incapacity extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'empleado_id',
        'sucursal_id',
        'puesto_id',
        'estatus_id',
        'fecha_inicio',
        'fecha_termino',
        'fecha_regreso',
        'comentarios'
    ];

    protected $appends = ['color'];

    public function getColorAttribute()
    {
        // Obtener el ID del empleado asociado al evento
        $employeeId = $this->empleado_id;

        // Usar el ID del empleado para generar un color único
        // Aquí se puede utilizar cualquier algoritmo de generación de colores
        // Por ejemplo, puedes convertir el ID a un valor hexadecimal y tomar solo los primeros 6 caracteres
        $color = substr(md5($employeeId), 0, 6);

        // Devolver el color en formato hexadecimal
        return '#' . $color;
    }

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['comentarios']);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function puesto()
    {
        return $this->belongsTo(Puesto::class, 'puesto_id');
    }

    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }
}
