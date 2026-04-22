<?php

namespace App\Models\Intranet;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contrapesos extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'contrapesos';

    protected $fillable = [
        'nro_parte',
        'descripcion',
        'trasero_delantero',
        'costo',
        'precio',
    ];

    public function tractorContrapesos()
    {
        return $this->belongsToMany(Product::class, 'tractor_contrapesos', 'contrapeso_id', 'product_id');
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nro_parte', 'descripcion']);
    }
}
