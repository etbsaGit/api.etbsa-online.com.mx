<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleTractor extends Model
{
    use HasFactory;

    use FilterableModel;

    protected $table = 'tractor_pago_detalle';

    protected $fillable = [
        'condicion_pago_id',
        'numero_pago',
        'monto_pago',
        'fecha_pago',
        'comments',
        'estatus_id',
        'metodo_pago_id'

    ];

    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['order']);
    }

    public function condicionPago()
    {
        return $this->belongsTo(CondicionPagoTractor::class, 'condicion_pago_id');
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id');
    }
    public function estatus(){
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }
}
