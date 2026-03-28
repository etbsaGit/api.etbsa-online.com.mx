<?php
namespace App\Models\Intranet;

use App\Models\Empleado;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tracking extends Model{
    use HasFactory;
    use FilterableModel;

    protected $table = 'tracking';

    protected $fillable = [
        'title',
        'folio',
        'cliente_id',
        'origen_track_id',
        'vendedor_id',
        'sucursal_id',
        'depto_id',
        'estatus_id',
        'certeza_id',
        'category_id',
        'condicion_pago_id',
        'currency_id',
        'subtotal',
        'iva',
        'tarifa_cambio',
        'descuento',
        'total',
        'factura',
        'date_lost_sale',
        'date_won_sale',
        'date_factura',
        'date_delivery'
    ];

    public function cliente(){
        return $this->belongsTo(Cliente::class,'cliente_id');
    }
    public function origen(){
        return $this->belongsTo(TrackingOrigen::class,'origen_track_id');
    }
    public function vendedor(){
        return $this->belongsTo(Empleado::class,'vendedor_id')
    }
}
