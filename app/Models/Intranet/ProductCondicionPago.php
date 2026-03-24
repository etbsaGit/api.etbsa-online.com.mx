<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCondicionPago extends Model
{
    use HasFactory;
    protected $table = 'products_condicion_pago';

    protected $fillable = [
        'name'
    ];

    public function condicioncategoria(){
        return $this->hasMany()
    }
}
