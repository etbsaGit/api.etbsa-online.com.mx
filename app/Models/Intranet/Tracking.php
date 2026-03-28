<?php
namespace App\Models\Intranet;

use App\Models\Empleado;
use App\Models\Sucursal;
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
        return $this->belongsTo(Empleado::class,'vendedor_id');
    }
    public function sucursal(){
        return $this->belongsTo(Sucursal::class,'sucursal_id');
    }
    public function certeza(){
        return $this->belongsTo(TrackingCerteza::class,'certeza_id');
    }
    public function categoria(){
        return $this->belongsTo(ProductCategory::class,'category_id');
    }
    public function condicionPago(){
        return $this->belongsTo(ProductCondicionPago::class,'condicion_pago_id');
    }
    public function currency(){
        return $this->belongsTo(Currency::class,'currency_id');
    }
    public function activities(){
        return $this->hasMany(TrackingActivity::class,'tracking_id');
    }

    public function scopeFilter(Builder $query,$filters){
        return $this->scopeFilterSearch($query,$filters,['title'],['folio']);
    }
}
