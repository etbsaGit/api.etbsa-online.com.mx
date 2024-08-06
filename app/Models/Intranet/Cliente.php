<?php

namespace App\Models\Intranet;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cliente extends Model
{
    use HasFactory;

    use FilterableModel;

    protected $table = 'clientes';

    protected $fillable = [
        'equip',
        'nombre',
        'tipo',
        'rfc',
        'curp',
        'telefono',
        'telefono_casa',
        'email',
        'state_entity_id',
        'town_id',
        'colonia',
        'calle',
        'codigo_postal',
        'classification_id',
        'segmentation_id',
        'technological_capability_id',
        'tactic_id',
        'construction_classification_id'
    ];

    public function stateEntity()
    {
        return $this->belongsTo(StateEntity::class, 'state_entity_id');
    }

    public function town()
    {
        return $this->belongsTo(Town::class, 'town_id');
    }

    public function classification()
    {
        return $this->belongsTo(Classification::class, 'classification_id');
    }

    public function segmentation()
    {
        return $this->belongsTo(Segmentation::class, 'segmentation_id');
    }

    public function technologicalCapability()
    {
        return $this->belongsTo(TechnologicalCapability::class, 'technological_capability_id');
    }

    public function tactic()
    {
        return $this->belongsTo(Tactic::class, 'tactic_id');
    }

    public function constructionClassification()
    {
        return $this->belongsTo(ConstructionClassification::class, 'construction_classification_id');
    }

    public function referencia()
    {
        return $this->hasMany(Referencia::class, 'cliente_id');
    }

    public function representante()
    {
        return $this->hasOne(Representante::class, 'cliente_id');
    }

    public function machine()
    {
        return $this->hasMany(Machine::class, 'cliente_id');
    }

    public function clienteTechnology()
    {
        return $this->hasMany(ClienteTechnology::class, 'cliente_id');
    }

    public function distribucion()
    {
        return $this->hasMany(Distribucion::class, 'cliente_id');
    }

    public function clienteCultivo()
    {
        return $this->hasMany(ClienteCultivo::class, 'cliente_id');
    }

    public function clienteRiego()
    {
        return $this->hasMany(ClienteRiego::class, 'cliente_id');
    }

    public function clienteAbastecimiento()
    {
        return $this->hasMany(ClienteAbastecimiento::class, 'cliente_id');
    }
}
