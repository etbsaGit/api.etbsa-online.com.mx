<?php

namespace App\Models\Intranet;

use App\Models\Estatus;
use App\Models\Empleado;
use App\Models\Sucursal;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    use FilterableModel;

    protected $fillable = [
       'amount',
       'comments',
       'feedback',
       'serial',
       'invoice',
       'order',
       'folio',
       'economic',
       'validated',
       'date',
       'cliente_id',
       'status_id',
       'referencia_id',
       'empleado_id',
       'sucursal_id'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function status()
    {
        return $this->belongsTo(Estatus::class, 'status_id');
    }

    public function referencia()
    {
        return $this->belongsTo(Referencia::class, 'referencia_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}
