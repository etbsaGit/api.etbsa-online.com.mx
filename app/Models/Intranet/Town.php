<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Town extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'state_entity_id'
    ];

    public function stateEntity()
    {
        return $this->belongsTo(StateEntity::class, 'state_entity_id');
    }

    public function cliente()
    {
        return $this->hasMany(Cliente::class, 'town_id');
    }

    public function representante()
    {
        return $this->hasMany(Representante::class, 'town_id');
    }
}
