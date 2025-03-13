<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Festivo extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'nombre',
        'fecha'
    ];

    // -Scope-
    // public function scopeFilter(Builder $query, array $filters)
    // {
    //     return $this->scopeFilterSearch($query, $filters, ['nombre','fecha']);
    // }

    // public function scopeFilter(Builder $query, $year)
    // {
    //     return $query->whereYear('fecha', $year);
    // }
}
