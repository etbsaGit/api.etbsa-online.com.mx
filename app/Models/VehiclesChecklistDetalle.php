<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehiclesChecklistDetalle extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'vehicles_checklist_detalle';

    protected $fillable = [
        'checklist_id',
        'documento',
        'existencia'
    ];

    public function checklist()
    {
        return $this->belongsTo(VehiclesChecklist::class, 'checklist_id');
    }
}
