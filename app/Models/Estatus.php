<?php

namespace App\Models;

use App\Models\Intranet\Sale;
use App\Models\Intranet\ClientesDoc;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Estatus extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'estatus';

    protected $fillable = [
        'nombre',
        'clave',
        'tipo_estatus',
        'color'
    ];

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre', 'tipo_estatus']);
    }

    public function documento()
    {
        return $this->hasMany(Documento::class, 'estatus_id');
    }

    public function empleado()
    {
        return $this->hasMany(Empleado::class, 'estatus_id');
    }

    public function termination()
    {
        return $this->hasMany(Termination::class, 'estatus_id');
    }

    public function reason()
    {
        return $this->hasMany(Termination::class, 'reason_id');
    }

    public function post()
    {
        return $this->hasMany(Post::class, 'estatus_id');
    }

    public function workOrder()
    {
        return $this->hasMany(WorkOrder::class, 'estatus_id');
    }

    public function workOrderTaller()
    {
        return $this->hasMany(WorkOrder::class, 'estatus_taller_id');
    }

    public function type()
    {
        return $this->hasMany(WorkOrder::class, 'type_id');
    }

    public function bay()
    {
        return $this->hasMany(Bay::class, 'estatus_id');
    }

    public function activityTechnician()
    {
        return $this->hasMany(ActivityTechnician::class, 'status_id');
    }

    public function clienteDoc()
    {
        return $this->hasMany(ClientesDoc::class, 'status_id');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'status_id');
    }
}
