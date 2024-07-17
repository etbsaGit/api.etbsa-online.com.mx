<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classification extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function cliente()
    {
        return $this->hasMany(Cliente::class, 'classification_id');
    }
}
