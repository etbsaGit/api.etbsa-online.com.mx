<?php

namespace App\Models\Intranet;

use App\Models\Ecommerce\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $table = 'currency';

    protected $fillable = ['name'];

    public function products(){
        return $this->hasMany(Product::class);
    }

}
