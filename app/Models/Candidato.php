<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Candidato extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'nombre',
        'telefono',
        'descripcion',
        'cv',
        'status_1',
        'fecha_entrevista_1',
        'forma_reclutamiento',
        'status_2',
        'fecha_ingreso',
        'requisicion_id',
    ];

    protected $appends = ['cvpath'];

    public function cvpath(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->cv ? Storage::disk('s3')->url($this->cv) : null
        );
    }

    protected function defaultPathFolder(): Attribute
    {
        return Attribute::make(
            get: fn() => "candidatos/" . $this->nombre . "/cv",
        );
    }

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre', 'telefono', 'cv', 'status_1', 'fecha_entrevista_1', 'forma_reclutamiento', 'status_2', 'fecha_ingreso']);
    }

    public function requisicion()
    {
        return $this->belongsTo(RequisicionPersonal::class, 'requisicion_id');
    }

    public function notas()
    {
        return $this->hasMany(CandidatoNota::class, 'candidato_id');
    }
}
