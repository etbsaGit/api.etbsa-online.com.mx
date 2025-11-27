<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SoftSkill extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'nombre',
        'definicion',
        'positivas',
        'negativas'
    ];

    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre', 'definicion', 'positivas', 'negativas']);
    }

    public function niveles()
    {
        return $this->hasMany(SoftSkillNivel::class, 'soft_skill_id');
    }
}
