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

class CreditoDocs extends Model
{
    use HasFactory;

    use FilterableModel;

    protected $table = 'credito_solicitud_archivos';

    protected $fillable = [
        'solicitud_id',
        'archivo',
        'path',
        'extension',
        'uploaded_by'
    ];

    protected $appends = ['realpath'];

    public function realpath(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->path ? Storage::disk('s3')->url($this->path) : null
        );
    }

    public function solicitud()
    {
        return $this->belongsTo(CreditoSolicitud::class, 'solicitud_id');
    }
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'uploaded_by');
    }
}
