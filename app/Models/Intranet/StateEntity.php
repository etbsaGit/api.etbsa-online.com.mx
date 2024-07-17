<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StateEntity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'abbreviation'
    ];

    public function town()
    {
        return $this->hasMany(Town::class, 'state_entity_id');
    }

    public function cliente()
    {
        return $this->hasMany(Cliente::class, 'state_entity_id');
    }

    public function representante()
    {
        return $this->hasMany(Representante::class, 'state_entity_id');
    }
}
