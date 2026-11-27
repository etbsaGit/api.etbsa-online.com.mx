<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehiclesDispersionNotificados extends Model
{
    use HasFactory;

    protected $table = 'vehicles_dispersion_notificados';

    protected $fillable = [
        'dispersion_id',
        'notificado_id',
    ];

    protected $casts = [
        'dispersion_id' => 'integer',
        'notificado_id' => 'integer',
    ];

    public function dispersion()
    {
        return $this->belongsTo(VehiclesDispersionGasolina::class, 'dispersion_id');
    }
    public function notificado()
    {
        return $this->belongsTo(Empleado::class, 'notificado_id');
    }
}
