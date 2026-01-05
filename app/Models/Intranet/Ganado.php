<?php

namespace App\Models\Intranet;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ganado extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'name',
    ];
    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['name']);
    }

    public function invercionesGanaderas()
    {
        return $this->hasMany(GanaderaInversion::class, 'ganado_id');
    }

    public function inverciones()
    {
        return $this->hasMany(InversionesGanadera::class, 'ganado_id');
    }
}
