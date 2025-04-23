<?php

namespace App\Models\Intranet;

use App\Models\EmpleadosContact;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kinship extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function referencia()
    {
        return $this->hasMany(Referencia::class, 'kinship_id');
    }

    // ---------------------------------Contacto de emergencia---------------------------------------------------------


    public function empleadosContact()
    {
        return $this->hasMany(EmpleadosContact::class, 'kinship_id');
    }
}
