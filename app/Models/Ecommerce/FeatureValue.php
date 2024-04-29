<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureValue extends Model
{
    use HasFactory;

    protected $table = 'feature_values';

    public $fillable = [
        'feature_id',
        'value'
    ];

    public function feature()
    {
        return $this->belongsTo(Features::class);
    }
}
