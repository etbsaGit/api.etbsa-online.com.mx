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
            get: function () {
                if (!$this->image_url) {
                    return null;
                }
                if (str_starts_with($this->image_url, 'http://') || str_starts_with($this->image_url, 'https://')) {
                    return $this->image_url;
                }
                return Storage::disk('s3')->url($this->image_url);
            }
        );
    }

    public function producto()
    {
        return $this->belongsTo(ProductsRiego::class, 'product_id');
    }
}
