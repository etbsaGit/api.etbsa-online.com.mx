<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Herramienta extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'nombre',
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre']);
    }

    public function requisiciones()
    {
        return $this->belongsToMany(RequisicionPersonal::class, 'p_requisicion_herramienta', 'herramienta_id', 'requisicion_id');
    }
}
