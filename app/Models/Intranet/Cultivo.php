<?php

namespace App\Models\Intranet;

use App\Models\ProspectCultivo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cultivo extends Model
{
    use HasFactory;

    protected $table = 'cultivos';

    protected $fillable = [
        'name',
    ];

    public function clienteCultivo()
    {
        return $this->hasMany(ClienteCultivo::class, 'cultivo_id');
    }

    public function prospectCultivo()
    {
        return $this->hasMany(ProspectCultivo::class, 'cultivo_id');
    }
}
