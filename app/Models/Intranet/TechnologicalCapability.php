<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnologicalCapability extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function cliente()
    {
        return $this->hasMany(Cliente::class, 'technological_capability_id');
    }
}
