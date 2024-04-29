<?php

namespace App\Models\Ecommerce;

use App\Casts\Name;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    use FilterableModel;
    use HasFactory;

    protected $table = 'brands';

    public $fillable = [
        'name',
        'slug',
        'logo',
    ];

    protected $casts = [
        'name' => Name::class,
    ];

    protected $appends = ['logopath'];


    public function logopath(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->logo ? Storage::disk('s3')->url($this->logo) : null
        );
    }

    protected function defaultPathFolder(): Attribute
    {
        return Attribute::make(
            get: fn () => "images/brands/id_" . $this->id,
        );
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
