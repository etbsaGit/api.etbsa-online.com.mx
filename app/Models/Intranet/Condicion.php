<?php

namespace App\Models\Intranet;

use App\Models\ProspectMaquina;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Condicion extends Model
{
    use HasFactory;

    protected $table = 'condiciones';

    protected $fillable = [
        'name',
    ];

    public function machine()
    {
        return $this->hasMany(Machine::class, 'condicion_id');
    }

    public function prospectMaquina()
    {
        return $this->hasMany(ProspectMaquina::class, 'condicion_id');
    }
}
