<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plantilla extends Model
{
    use HasFactory;

    protected $table = 'plantillas';

    protected $fillable = [
        'nombre'
    ];

    public function requisito()
    {
        return $this->belongsToMany(Requisito::class, 'p_plantillas_requisitos', 'plantilla_id', 'requisito_id')->withTimestamps();
    }

}
