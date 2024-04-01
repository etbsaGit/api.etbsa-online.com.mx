<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Qualification extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'linea_technician_id',
    ];

    public function lineaTechnician()
    {
        return $this->belongsTo(LineaTechnician::class, 'linea_technician_id');
    }

    public function empleado()
    {
        return $this->belongsToMany(Empleado::class, 'p_empleado_qualification', 'empleado_id', 'qualification_id');
    }
}
