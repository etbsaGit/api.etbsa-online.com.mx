<?php

namespace App\Models\Intranet;

use App\Traits\FilterableModel;
use App\Models\EmpleadosContact;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kinship extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'name',
    ];
    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['name']);
    }

    public function referencia()
    {
        return $this->hasMany(Referencia::class, 'kinship_id');
    }

    // ---------------------------------Contacto de emergencia---------------------------------------------------------


    public function empleadosContact()
    {
        return $this->hasMany(EmpleadosContact::class, 'kinship_id');
    }
}
