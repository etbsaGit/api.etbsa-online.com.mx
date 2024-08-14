<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechniciansLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha',
        'hora_inicio',
        'hora_termino',
        'comentarios',
        'tecnico_id',
        'wo_id',
        'activity_technician_id'
    ];

    public function tecnico()
    {
        return $this->belongsTo(Empleado::class, 'tecnico_id');
    }

    public function wo()
    {
        return $this->belongsTo(WorkOrder::class, 'wo_id');
    }

    public function activityTechnician()
    {
        return $this->belongsTo(ActivityTechnician::class, 'activity_technician_id');
    }
}
