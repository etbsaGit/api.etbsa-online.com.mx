<?php

namespace App\Models;

use Carbon\Carbon;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Visit extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'dia',
        'ubicacion',
        'prospect_id',
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
        // Filtrado por 'month' y 'year' si existen
        if (isset($filters['month']) && isset($filters['year'])) {
            $startDate = Carbon::create($filters['year'], $filters['month'], 1)->startOfMonth();
            $endDate = Carbon::create($filters['year'], $filters['month'], 1)->endOfMonth();

            $query->whereBetween('dia', [$startDate, $endDate]);
        }

        // Filtrado por 'empleado_id' si está presente
        if (isset($filters['empleado_id'])) {
            $query->where('empleado_id', $filters['empleado_id']);
        }

        return $query;
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function prospect()
    {
        return $this->belongsTo(Prospect::class, 'prospect_id');
    }
}
