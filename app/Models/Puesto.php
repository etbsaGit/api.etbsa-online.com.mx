<?php

namespace App\Models;

use App\Models\Empleado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Puesto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre'
    ];

    public function empleado()
    {
        return $this->hasMany(Empleado::class, 'puesto_id');
    }

    public function experienciaLaboral()
    {
        return $this->hasMany(ExperienciaLaboral::class, 'puesto_id');
    }

    public function skill()
    {
        return $this->belongsToMany(Skill::class, 'p_skills_puestos', 'puesto_id', 'skill_id');
    }
}
