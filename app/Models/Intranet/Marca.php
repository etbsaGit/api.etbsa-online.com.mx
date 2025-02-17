<?php

namespace App\Models\Intranet;

use App\Models\ProspectMaquina;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Marca extends Model
{
    use HasFactory;

    protected $table = 'marcas';

    protected $fillable = [
        'name',
    ];

    public function machine()
    {
        return $this->hasMany(Machine::class, 'marca_id');
    }

    public function prospectMaquina()
    {
        return $this->hasMany(ProspectMaquina::class, 'marca_id');
    }
}
