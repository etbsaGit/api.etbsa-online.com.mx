<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnologicalCapability extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'level',
    ];

    public function clientes()
    {
        return $this->belongsToMany(Cliente::class, 'p_clientes_technological_capabilities');
    }

}
