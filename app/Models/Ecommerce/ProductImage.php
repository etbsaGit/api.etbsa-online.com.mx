<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute as CastableAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $table = 'product_images';

    protected $fillable = ['product_id', 'thumbnail', 'path'];

    protected $casts = [
        'product_id' => 'integer',
        // 'main' => 'boolean',
    ];

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function path(): CastableAttribute
    {
        return CastableAttribute::make(
            get: static function($value) {
                if(!is_null($value)) {
                    return Storage::disk('s3')->url($value);
                }
            }
        );
    }

    public function storagePath(): CastableAttribute
    {
        return CastableAttribute::make(
            get: static function($value, $attributes) {
                if(!is_null($attributes['path'])) {
                    return $attributes['path'];
                }

                return null;
            }
        );
    }
}
