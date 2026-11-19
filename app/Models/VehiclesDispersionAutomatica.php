<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehiclesDispersionAutomatica extends Model
{
    use HasFactory;

    protected $table = 'vehicles_dispersion_automatica';

    protected $fillable = [
        'vehicle_id',
        'lts_cargar',
    ];

    protected $casts = [
        'vehicle_id' => 'integer',
        'lts_cargar' => 'integer',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
