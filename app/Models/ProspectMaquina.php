<?php

namespace App\Models;

use App\Models\Intranet\Marca;
use App\Models\Intranet\Condicion;
use App\Models\Intranet\ClasEquipo;
use App\Models\Intranet\TipoEquipo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProspectMaquina extends Model
{
    use HasFactory;

    protected $fillable = [
        'modelo',
        'anio',
        'prospect_id',
        'marca_id',
        'condicion_id',
        'clas_equipo_id',
        'tipo_equipo_id',
    ];

    public function prospect()
    {
        return $this->belongsTo(Prospect::class, 'prospect_id');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function condicion()
    {
        return $this->belongsTo(Condicion::class, 'condicion_id');
    }

    public function clasEquipo()
    {
        return $this->belongsTo(ClasEquipo::class, 'clas_equipo_id');
    }

    public function tipoEquipo()
    {
        return $this->belongsTo(TipoEquipo::class, 'tipo_equipo_id');
    }
}
