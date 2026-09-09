<?php

namespace App\Models\Intranet\CreditoInterno;

use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Intranet\Cliente;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class CreditoTipoEnganche extends Model
{
    use HasFactory;

    use FilterableModel;

    protected $table = 'credito_tipo_enganche';

    protected $fillable = [
        'nombre',
    ];

    public function solicitudes()
    {
        return $this->hasMany(CreditoSolicitud::class, 'tipo_enganche_id');
    }

    public function lineas()
    {
        return $this->belongsToMany(CreditoLineas::class, 'credito_lineas_enganche', 'tipo_id', 'linea_id');
    }
}
