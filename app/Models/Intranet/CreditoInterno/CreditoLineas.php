<?php

namespace App\Models\Intranet\CreditoInterno;

use App\Models\Intranet\CreditoInterno\CreditoSolicitud;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditoLineas extends Model
{
    use HasFactory;

    use FilterableModel;

    protected $table = 'credito_lineas';

    protected $fillable = [
        'name'
    ];

    public function solicitudes()
    {
        return $this->hasMany(CreditoSolicitud::class, 'linea_id');
    }

    public function creditosCliente()
    {
        return $this->hasMany(CreditoLineaCliente::class, 'linea_id');
    }

    public function tiposEnganche()
    {
        return $this->belongsToMany(CreditoTipoEnganche::class, 'credito_lineas_enganche', 'linea_id', 'tipo_id');
    }
}
