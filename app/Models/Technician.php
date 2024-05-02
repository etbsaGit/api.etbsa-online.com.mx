<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Technician extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'level',
        'antiguedad_minima',
        'jobcode',
        'levelcap'
    ];

    protected $appends = ['concepto'];

    public function getConceptoAttribute()
    {
        return "Categoria: " . $this->name . ' / ' . "Antigüedad minima: " . $this->antiguedad_minima . ' / ' . "Level Cap: ".$this->levelcap . ' / ' . "Job Code: ".$this->jobcode;
    }

    public function empleado()
    {
        return $this->hasMany(Empleado::class, 'technician_id');
    }

    public function lineaTechnician()
    {
        return $this->hasMany(LineaTechnician::class, 'technician_id');
    }
}
