<?php

namespace App\Models;

use App\Traits\FilterableModel;
use App\Models\Ecommerce\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Propuesta extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'title',
        'description',
        'status',
        'notas',
        'image',
        'url',
        'start_date',
        'end_date',
        'inversion',
        'estatus_id',
        'linea_id',
        'departamento_id',
        'created_by',
        'auth_by',
        'auth_at',
        'category_id'
    ];

    protected $appends = ['pic'];

    public function pic(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->image ? Storage::disk('s3')->url($this->image) : null
        );
    }

    protected function defaultPathFolder(): Attribute
    {
        return Attribute::make(
            get: fn() => "propuesta/" . $this->id . "/imagen",
        );
    }

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['title', 'description']);
    }

    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }

    public function linea()
    {
        return $this->belongsTo(Linea::class, 'linea_id');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function auth()
    {
        return $this->belongsTo(User::class, 'auth_by');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
