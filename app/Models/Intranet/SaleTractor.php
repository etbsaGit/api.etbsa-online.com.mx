<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Intranet\Cliente;
use App\Models\Empleado;
use App\Models\Sucursal;
use App\Models\Estatus;
use App\Traits\FilterableModel;

class SaleTractor extends Model
{
    use HasFactory;
    use FilterableModel;


    protected $table = 'sales_tractors';

    protected $fillable = [
        'order',
        'vendedor_id',
        'sucursal_id',
        'cliente_id',
        'inv_model_id',
        'fecha',
        'total',
        'condicion_pago',
        'referencia_cliente_id',
        'comments',
        'estatus_id',
    ];

    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['order']);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(Empleado::class, 'vendedor_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function invModel()
    {
        return $this->belongsTo(InvModel::class, 'inv_model_id');
    }

    public function referencia()
    {
        return $this->belongsTo(Referencia::class, 'referencia_id');
    }

    public function estatus(){
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }

}
