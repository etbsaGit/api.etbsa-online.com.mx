<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehiclesTipos extends Model
{
    use HasFactory, FilterableModel;
    protected $table = 'vehicles_tipos';
    protected $fillable = [
        'nombre'
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre']);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'tipo_id', 'id');
    }
}
