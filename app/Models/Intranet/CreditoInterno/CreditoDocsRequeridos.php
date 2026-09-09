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

class CreditoDocsRequeridos extends Model
{
    use HasFactory;

    use FilterableModel;

    protected $table = 'credito_solicitud_docs_requeridos';

    protected $fillable = [
        'nombre',
        'doc_id'
    ];
}
