<?php

namespace App\Models\Intranet;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrackingProspecto extends Model {
    use HasFactory;
    use FilterableModel;

    protected $table = 'tracking_prospectos';

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'telefono_casa',
        'ubicacion',
    ];

    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['name']);
    }

}
