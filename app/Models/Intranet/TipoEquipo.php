<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoEquipo extends Model
{
    use HasFactory;

    protected $table = 'tipos_equipo';

    protected $fillable = [
        'name',
    ];

    public function machine()
    {
        return $this->hasMany(Machine::class, 'tipo_equipo_id');
    }
}
