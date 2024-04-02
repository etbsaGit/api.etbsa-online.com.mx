<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function puesto()
    {
        return $this->belongsToMany(Puesto::class, 'p_skills_puestos', 'skill_id', 'puesto_id');
    }

    public function skillRating()
    {
        return $this->hasMany(SkillRating::class, 'skill_id');
    }
}
