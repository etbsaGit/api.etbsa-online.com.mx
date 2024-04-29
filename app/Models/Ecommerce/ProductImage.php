<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $table = 'product_images';

    protected $fillable = ['product_id', 'thumbnail', 'path'];

    protected $appends = ['realpath'];

    protected $casts = [
        'product_id' => 'integer',
        // 'main' => 'boolean',
    ];

    public function realpath(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->path ? Storage::disk('s3')->url($this->path) : null
        );
    }

    protected function defaultPathFolder(): Attribute
    {
        return Attribute::make(
            get: fn () => "images/products/id_" . $this->product_id,
        );
    }


    public function product() {
        return $this->belongsTo(Product::class);
    }

}
