<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoftSkillEmpleado extends Model
{
    use HasFactory;

    protected $table = 'soft_skill_empleados';

    protected $fillable = [
        'definicion',
        'evidencia',
        'empleado_id',
        'soft_skill_id',
        'soft_skill_nivel_id'
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function softSkill()
    {
        return $this->belongsTo(SoftSkill::class, 'soft_skill_id');
    }

    public function nivel()
    {
        return $this->belongsTo(SoftSkillNivel::class, 'soft_skill_nivel_id');
    }
}
