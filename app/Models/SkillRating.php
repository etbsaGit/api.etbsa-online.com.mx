<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkillRating extends Model
{
    use HasFactory;

    protected $table = 'skill_rating';

    protected $fillable = [
        'rating',
        'skill_id',
        'empleado_id'
    ];

    public function skill()
    {
        return $this->belongsTo(Skill::class, 'skill_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }
}
