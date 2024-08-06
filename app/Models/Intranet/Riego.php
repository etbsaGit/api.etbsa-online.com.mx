<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Riego extends Model
{
    use HasFactory;

    protected $table = 'riegos';

    protected $fillable = [
        'name',
    ];

    public function clienteRiego()
    {
        return $this->hasMany(ClienteRiego::class, 'riego_id');
    }
}
