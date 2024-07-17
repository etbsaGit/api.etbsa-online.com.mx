<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCultivo extends Model
{
    use HasFactory;

    protected $table = 'tipos_cultivo';

    protected $fillable = [
        'name',
    ];

    public function clienteCultivo()
    {
        return $this->hasMany(ClienteCultivo::class, 'tipo_cultivo_id');
    }
}
