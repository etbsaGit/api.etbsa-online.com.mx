<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Visit extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'dia',
        'cliente',
        'ubicacion',
        'telefono',
        'cultivos',
        'hectareas',
        'maquinaria',
        'empleado_id',
        'comentarios',
        'retroalimentacion'
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
        return $this->scopeFilterSearch($query, $filters, ['dia', 'cliente', 'ubicacion', 'telefono', 'cultivos', 'hectareas', 'maquinaria',]);
    }

    public function getCultivosAttribute($value)
    {
        // Si el valor no es nulo, convertirlo en un array de strings en minúsculas
        return $value ? array_map('strtolower', array_map('trim', explode(', ', $value))) : [];
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }
}
