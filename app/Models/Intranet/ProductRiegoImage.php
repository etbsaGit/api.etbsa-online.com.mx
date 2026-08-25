<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class ProductRiegoImage extends Model
{
    use HasFactory;

    protected $table = 'products_riego_images';

    protected $fillable = [
        'product_id',
        'image_url',
    ];

    protected $appends = ['url'];

    public function url(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->image_url ? Storage::disk('s3')->url($this->image_url) : null
        );
    }

    public function producto()
    {
        return $this->belongsTo(ProductsRiego::class, 'product_id');
    }
}
