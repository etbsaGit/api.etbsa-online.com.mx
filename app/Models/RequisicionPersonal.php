<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequisicionPersonal extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'sexo',
        'rango_edad',
        'habilidades',
        'idiomas',
        'manejo_equipo',
        'sueldo_mensual_inicial',
        'comisiones',
        'experiencia_conocimientos',
        'actividades_desempenar',
        'total_posiciones',
        'tipo_vacante',
        'motivo_vacante',
        'especificar_vacante',
        'puesto_id',
        'sucursal_id',
        'linea_id',
        'departamento_id',
        'escolaridad_id',
        'solicita_id',
        'autoriza_id',
        'vo_bo_id',
        'recibe_id',
        'autorizacion',
        'estatus',
        'auth_by',
        'path',
    ];

    protected $appends = ['realpath', 'count'];

    public function realpath(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->path ? Storage::disk('s3')->url($this->path) : null
        );
    }

    protected function defaultPathFolder(): Attribute
    {
        return Attribute::make(
            get: fn() => "requisicion/" . $this->id,
        );
    }

    public function count(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->candidatos()
                ->where('status_1', 'Postulado desde la bolsa de trabajo')
                ->count()
        );
    }




    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre']);
    }

    // Relaciones
    public function competencias()
    {
        return $this->belongsToMany(Competencia::class, 'p_requisicion_competencia', 'requisicion_id', 'competencia_id');
    }

    public function herramientas()
    {
        return $this->belongsToMany(Herramienta::class, 'p_requisicion_herramienta', 'requisicion_id', 'herramienta_id');
    }

    public function puesto()
    {
        return $this->belongsTo(Puesto::class, 'puesto_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function linea()
    {
        return $this->belongsTo(Linea::class, 'linea_id');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function escolaridad()
    {
        return $this->belongsTo(Escolaridad::class, 'escolaridad_id');
    }

    public function solicita()
    {
        return $this->belongsTo(Empleado::class, 'solicita_id');
    }

    public function autoriza()
    {
        return $this->belongsTo(Empleado::class, 'autoriza_id');
    }

    public function voBo()
    {
        return $this->belongsTo(Empleado::class, 'vo_bo_id');
    }

    public function recibe()
    {
        return $this->belongsTo(Empleado::class, 'recibe_id');
    }

    public function auth()
    {
        return $this->belongsTo(Empleado::class, 'auth_by');
    }

    public function candidatos()
    {
        return $this->hasMany(Candidato::class, 'requisicion_id');
    }
}
