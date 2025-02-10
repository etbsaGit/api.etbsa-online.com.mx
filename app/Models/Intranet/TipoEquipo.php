<?php

namespace App\Models\Intranet;

use App\Models\ProspectMaquina;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function prospectMaquina()
    {
        return $this->hasMany(ProspectMaquina::class, 'tipo_equipo_id');
    }
}
