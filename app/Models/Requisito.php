<?php

namespace App\Models;

use App\Models\Documento;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Requisito extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre']);
    }

    public function documento()
    {
        return $this->hasMany(Documento::class, 'requisito_id');
    }

    public function plantilla()
    {
        return $this->belongsToMany(Plantilla::class, 'p_plantillas_requisitos', 'plantilla_id', 'requisito_id')->withTimestamps();
    }

    public function expediente()
    {
        return $this->belongsToMany(Expediente::class, 'documentos')->using(Documento::class)->withPivot('fecha_de_vencimiento', 'comentario', 'estatus_id', 'id')->withTimestamps();
    }
}
