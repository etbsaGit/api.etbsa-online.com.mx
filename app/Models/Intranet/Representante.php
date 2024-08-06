<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Representante extends Model
{
    use HasFactory;

    protected $table = 'representantes';

    protected $fillable = [
        'nombre',
        'rfc',
        'telefono',
        'email',
        'state_entity_id',
        'town_id',
        'colonia',
        'calle',
        'codigo_postal',
        'cliente_id',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function stateEntity()
    {
        return $this->belongsTo(StateEntity::class, 'state_entity_id');
    }

    public function town()
    {
        return $this->belongsTo(Town::class, 'town_id');
    }
}
