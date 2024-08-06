<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConstructionClassification extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function cliente()
    {
        return $this->hasMany(Cliente::class, 'construction_classification_id');
    }
}
