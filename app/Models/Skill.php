<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Skill extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'name',
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre']);
    }

    public function puesto()
    {
        return $this->belongsToMany(Puesto::class, 'p_skills_puestos', 'skill_id', 'puesto_id');
    }

    public function skillRating()
    {
        return $this->hasMany(SkillRating::class, 'skill_id');
    }
}
