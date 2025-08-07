<?php

namespace App\Models\Ecommerce;

use App\Models\Propuesta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    public $fillable = [
        'name',
        'slug',
        'logo',
        'parent_id'
    ];

    protected $appends = ['logopath'];

    public function logopath(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->logo ? Storage::disk('s3')->url($this->logo) : null
        );
    }

    protected function defaultPathFolder(): Attribute
    {
        return Attribute::make(
            get: fn() => "images/vendors/id_" . $this->id,
        );
    }


    /**
     * Return the children of the model, if exists.
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->select(['id', 'parent_id', 'name', 'slug']);
    }

    /**
     * Return the parents of the model, if exists.
     * @return HasMany
     */
    public function parent(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Returns the categories of each category, recursively
     * @return HasMany
     */
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with(['childrenRecursive']);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_categories', 'category_id', 'product_id');
    }

    public function propuestas()
    {
        return $this->hasMany(Propuesta::class, 'category_id');
    }
}
