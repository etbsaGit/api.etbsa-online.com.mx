<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_time',
        'end_time',
        'date',
        'sucursal_id',
        'empleado_id'
    ];

    protected $appends = ['countActivities', 'color'];

    // public function getDateAttribute($value)
    // {
    //     return Carbon::parse($value)->format('d-m-Y');
    // }

    // public function getEndTimeAttribute($value)
    // {
    //     return Carbon::createFromFormat('H:i:s', $value)->format('H:i');
    // }

    // public function getStartTimeAttribute($value)
    // {
    //     return Carbon::createFromFormat('H:i:s', $value)->format('H:i');
    // }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function activity()
    {
        return $this->hasMany(Activity::class, 'event_id');
    }

    public function getCountActivitiesAttribute()
    {
        // Obtener actividades completadas y no completadas
        $completedActivities = $this->activity()->where('completed', 1)->get();
        $incompleteActivities = $this->activity()->where('completed', 0)->get();

        // Contar las actividades
        $completedCount = $completedActivities->count();
        $incompleteCount = $incompleteActivities->count();

        // Retornar los resultados
        return [
            // 'completed_activities' => $completedActivities,
            'completed_count' => $completedCount,
            // 'incomplete_activities' => $incompleteActivities,
            'incomplete_count' => $incompleteCount
        ];
    }

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
}
