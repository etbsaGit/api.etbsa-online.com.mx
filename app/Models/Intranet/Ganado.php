<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ganado extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function invercionesGanaderas()
    {
        return $this->hasMany(GanaderaInversion::class, 'ganado_id');
    }

    public function inverciones()
    {
        return $this->hasMany(InversionesGanadera::class, 'ganado_id');
    }
}
