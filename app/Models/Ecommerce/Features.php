<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Features extends Model
{
    use HasFactory;


    protected $table = 'features';

    public $fillable = [
        'name',
    ];

    public function values()
    {
        return $this->hasMany(FeatureValue::class, 'feature_id');
    }
}
