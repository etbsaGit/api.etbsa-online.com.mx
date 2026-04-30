<?php

namespace App\Models\Intranet;

use App\Models\Intranet\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoPrecio extends Model
{
    use HasFactory;

    protected $table = 'precio_producto';

    protected $fillable = [
        'precio',
        'condicion_pago_id',
        'producto_id',
        'currency_id'
    ];

    public function producto(){
        return $this->belongsTo(Product::class,'producto_id');
    }

    public function condicionPago(){
        return $this->belongsTo(ProductCondicionPago::class,'condicion_pago_id');
    }

    public function currency(){
        return $this->belongsTo(Currency::class,'currency_id');
    }
}
