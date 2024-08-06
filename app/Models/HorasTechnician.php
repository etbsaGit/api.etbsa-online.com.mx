<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorasTechnician extends Model
{
    use HasFactory;

    protected $table = 'horas_technicians';

    protected $fillable = [
        'mes',
        'anio',
        'facturadas',
        'con_ingreso',
        'tecnico_id'
    ];

    public function horasFacturadas()
    {
        return $this->belongsTo(Empleado::class, 'tecnico_id');
    }
}
