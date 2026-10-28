<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CatUsosCfdi extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'cat_usos_cfdi';

    protected $fillable = [
        'nombre',
        'clave'
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre', 'clave']);
    }
}
