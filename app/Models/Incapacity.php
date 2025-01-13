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
        'folio',
        'inicial',
        'empleado_id',
        'sucursal_id',
        'puesto_id',
        'estatus_id',
        'fecha_inicio',
        'fecha_termino',
        'fecha_regreso',
        'comentarios',
        'incapacity_id'
    ];

    protected $appends = ['color', 'total', 'latestDate'];

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

    public function getTotalAttribute()
    {
        // Calcula los días del registro principal
        $parentDays = 0;
        if (!empty($this->fecha_inicio) && !empty($this->fecha_termino)) {
            $parentDays = \Carbon\Carbon::parse($this->fecha_inicio)
                ->diffInDays(\Carbon\Carbon::parse($this->fecha_termino)) + 1; // +1 para incluir el día final
        }

        // Calcula los días de los hijos
        $childrenDays = $this->children
            ->map(function ($child) {
                if (!empty($child['fecha_inicio']) && !empty($child['fecha_termino'])) {
                    return \Carbon\Carbon::parse($child['fecha_inicio'])
                        ->diffInDays(\Carbon\Carbon::parse($child['fecha_termino'])) + 1;
                }
                return 0;
            })
            ->sum();

        // Retorna el total
        return $parentDays + $childrenDays;
    }

    public function getLatestDateAttribute()
    {
        // Obtén la fecha de regreso del padre como un objeto Carbon, o null si no existe
        $latestReturnDate = !empty($this->fecha_regreso)
            ? \Carbon\Carbon::parse($this->fecha_regreso)
            : null;

        // Recorre las fechas de regreso de los hijos
        foreach ($this->children as $child) {
            if (!empty($child['fecha_regreso'])) {
                $childReturnDate = \Carbon\Carbon::parse($child['fecha_regreso']);

                // Si no hay fecha principal o la fecha del hijo es más grande, actualízala
                if ($latestReturnDate === null || $childReturnDate->greaterThan($latestReturnDate)) {
                    $latestReturnDate = $childReturnDate;
                }
            }
        }

        // Devuelve la fecha más lejana como cadena, o null si no hay fechas
        return $latestReturnDate ? $latestReturnDate->toDateString() : null;
    }


    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['folio', 'comentarios']);
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

    /**
     * Relación: Incapacidad padre.
     * Cada incapacidad puede pertenecer a una incapacidad "padre".
     */
    public function parent()
    {
        return $this->belongsTo(Incapacity::class, 'incapacity_id');
    }

    /**
     * Relación: Incapacidades hijas.
     * Cada incapacidad puede tener muchas incapacidades "hijas".
     */
    public function children()
    {
        return $this->hasMany(Incapacity::class, 'incapacity_id');
    }
}
