<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoftSkillNivel extends Model
{
    use HasFactory;

    protected $table = 'soft_skill_niveles';

    protected $fillable = [
        'nombre',
        'descripcion',
        'indicadores',
        'nivel',
        'soft_skill_id'
    ];

    public function softSkill()
    {
        return $this->belongsTo(SoftSkill::class, 'soft_skill_id');
    }

    // ---------------------------------SoftSkillEmpleado---------------------------------------------------------
    public function softSkillEmpleado()
    {
        return $this->hasMany(SoftSkillEmpleado::class, 'soft_skill_nivel_id');
    }
}
