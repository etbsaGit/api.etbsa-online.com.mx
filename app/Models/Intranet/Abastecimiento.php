<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abastecimiento extends Model
{
    use HasFactory;

    protected $table = 'abastecimientos';

    protected $fillable = [
        'name',
    ];

    public function clienteAbastecimiento()
    {
        return $this->hasMany(ClienteAbastecimiento::class, 'abastecimiento_id');
    }

}
