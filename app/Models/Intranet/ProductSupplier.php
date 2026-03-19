<?php

namespace App\Models\Intranet;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductSupplier extends Model
{
    use FilterableModel;
    use HasFactory;

    protected $table = 'vendors';

    public $fillable = [
        'name',

    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    //filtros
    public function scopeFilter($query, $filters)
    {
        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }
        return $query;
    }
}
