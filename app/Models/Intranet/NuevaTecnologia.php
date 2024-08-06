<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NuevaTecnologia extends Model
{
    use HasFactory;

    protected $table = 'nuevas_tecnologias';

    protected $fillable = [
        'name',
    ];

    public function clienteTechnology()
    {
        return $this->hasMany(ClienteTechnology::class, 'nueva_tecnologia_id');
    }
}
