<?php

namespace App\Models\Caja;

use App\Models\User;
use App\Traits\FilterableModel;
use App\Models\Intranet\Cliente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CajaTransaccion extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'caja_transacciones';

    protected $fillable = [
        'factura',
        'fecha_pago',
        'folio',
        'serie',
        'uuid',
        'comentarios',
        'validado',
        'iva',
        'cliente_id',
        'user_id',
        'tipo_factura_id',
        'cuenta_id',
        'tipo_pago_id',
    ];

    protected $appends = ['total'];

    protected function total(): Attribute
    {
        return Attribute::get(function () {
            $totalSinIva = $this->pagos->sum('monto');

            if ($this->iva) {
                $iva = round($totalSinIva * 0.16, 2);
            } else {
                $iva = 0;
            }

            $totalConIva = round($totalSinIva + $iva, 2);

            return (object) [
                'total_sin_iva' => round($totalSinIva, 2),
                'iva' => $iva,
                'total_con_iva' => $totalConIva,
            ];
        });
    }

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['factura', 'folio', 'serie', 'uuid']);
    }

    public function cliente()
    {
        return $this->belongsTo(CajaCliente::class, 'cliente_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tipoFactura()
    {
        return $this->belongsTo(CajaTiposFactura::class, 'tipo_factura_id');
    }

    public function cuenta()
    {
        return $this->belongsTo(CajaCuenta::class, 'cuenta_id');
    }

    public function tipoPago()
    {
        return $this->belongsTo(CajaTiposPago::class, 'tipo_pago_id');
    }

    // ---------------------------------Pago---------------------------------------------------------

    public function pagos()
    {
        return $this->hasMany(CajaPago::class, 'transaccion_id');
    }
}
