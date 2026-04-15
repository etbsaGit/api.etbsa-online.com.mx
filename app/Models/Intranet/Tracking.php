<?php

namespace App\Models\Intranet;

use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Sucursal;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tracking extends Model
{
    use HasFactory;
    use FilterableModel;

    protected $table = 'tracking';

    protected $fillable = [
        'folio',
        'cliente_id',
        'prospecto_id',
        'origen_track_id',
        'vendedor_id',
        'sucursal_id',
        'depto_id',
        'estatus_id',
        'category_id',
        'condicion_pago_id',
        'currency_id',
        'subtotal',
        'iva_monto',
        'incluye_iva',
        'tarifa_cambio',
        'descuento',
        'total',
        'factura',
        'date_lost_sale',
        'date_won_sale',
        'date_factura',
        'date_delivery',
        'notas'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
    public function prospecto()
    {
        return $this->belongsTo(TrackingProspecto::class, 'prospecto_id');
    }
    public function origen()
    {
        return $this->belongsTo(TrackingOrigen::class, 'origen_track_id');
    }
    public function vendedor()
    {
        return $this->belongsTo(Empleado::class, 'vendedor_id');
    }
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
    public function categoria()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
    public function condicionPago()
    {
        return $this->belongsTo(ProductCondicionPago::class, 'condicion_pago_id');
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
    public function activities()
    {
        return $this->hasMany(TrackingActivity::class, 'tracking_id');
    }
    public function detalles()
    {
        return $this->hasMany(TrackingDetalle::class, 'tracking_id');
    }
    public function extras()
    {
        return $this->hasMany(TrackingDetalleExtras::class, 'tracking_id');
    }
    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }
    public function depto()
    {
        return $this->belongsTo(TrackingDepto::class, 'depto_id');
    }
    public function ultimaActividad()
    {
        return $this->hasOne(TrackingActivity::class)->latestOfMany();
    }
    public function scopeFilter(Builder $query, $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['folio']);
    }
}
