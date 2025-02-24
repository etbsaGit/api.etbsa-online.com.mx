<?php

namespace App\Models;

use App\Models\Intranet\TipoEquipo;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Used extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'name',
        'description',
        'comments',
        'serial',
        'status',
        'year',
        'hours',
        'cost',
        'price',
        'origin_id',
        'location_id',
        'tipo_equipo_id',
        'linea_id'
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['name', 'description', 'comments', 'serial']);
    }

    public function origin()
    {
        return $this->belongsTo(Sucursal::class, 'origin_id');
    }

    public function location()
    {
        return $this->belongsTo(Sucursal::class, 'location_id');
    }

    public function tipoEquipo()
    {
        return $this->belongsTo(TipoEquipo::class, 'tipo_equipo_id');
    }

    public function linea()
    {
        return $this->belongsTo(Linea::class, 'linea_id');
    }

    public function usedDoc()
    {
        return $this->hasMany(UsedDoc::class, 'used_id');
    }


}
