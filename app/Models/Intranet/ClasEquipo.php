<?php

namespace App\Models\Intranet;

use App\Models\ProspectMaquina;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClasEquipo extends Model
{
    use HasFactory;

    protected $table = 'clas_equipos';

    protected $fillable = [
        'name',
    ];

    public function machine()
    {
        return $this->hasMany(Machine::class, 'clas_equipo_id');
    }

    public function prospectMaquina()
    {
        return $this->hasMany(ProspectMaquina::class, 'clas_equipo_id');
    }

}
