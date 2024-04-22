<?php

namespace App\Models\Ecommerce;

use App\Casts\Name;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Casts\Attribute as CastableAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Vendor extends Model
{
    use FilterableModel;
    use HasFactory;

    protected $table = 'vendors';

    public $fillable = [
        'name',
        'slug',
        'logo',
    ];

    protected $casts = [
        'name' => Name::class,
    ];


    /**
     *
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(static function ($vendor) {
            $vendor->slug = \Str::slug($vendor->name);
            $vendor->save();
        });
        // static::updated(static function ($vendor) {
        //     $vendor->slug = \Str::slug($vendor->name);
        //     $vendor->save();
        // });
    }

    public function logo(): CastableAttribute
    {
        return CastableAttribute::make(
            get: static function($value) {
                if(!is_null($value)) {
                    // return asset('storage/' . $value);
                    return Storage::disk('s3')->url($value);
                }
            }
        );
    }

    public function storageLogo(): CastableAttribute
    {
        return CastableAttribute::make(
            get: static function($value, $attributes) {
                if(!is_null($attributes['logo'])) {
                    return $attributes['logo'];
                }

                return null;
            }
        );
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
