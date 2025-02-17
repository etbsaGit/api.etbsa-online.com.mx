<?php

namespace App\Models\Intranet;

use App\Models\ProspectCultivo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function prospectCultivo()
    {
        return $this->hasMany(ProspectCultivo::class, 'tipo_cultivo_id');
    }
}
