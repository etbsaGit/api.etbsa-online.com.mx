<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrecioProdRiego extends Model
{
    use HasFactory;

    protected $table = 'precio_prod_riego';
    protected $fillable = [
        'producto_id',
        'precio',
        'nivel_partner_id',
        'currency_id'
    ];

    public function producto()
    {
        return $this->belongsTo(ProductsRiego::class, 'producto_id');
    }

    public function nivelPartner()
    {
        return $this->belongsTo(NivelPartner::class, 'nivel_partner_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
