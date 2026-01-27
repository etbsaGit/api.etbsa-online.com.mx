<?php

namespace App\Models\Intranet;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvModel extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'code',
        'name',
        'description',
        'price',
        'path',
        'tipo_equipo_id',
        'clas_equipo_id',
    ];

    protected $appends = ['realpath'];

    public function realpath(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->path ? Storage::disk('s3')->url($this->path) : null
        );
    }

    protected function defaultPathFolder(): Attribute
    {
        return Attribute::make(
            get: fn() => "intranet/modelo/" . $this->code,
        );
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['code', 'name', 'description', 'price',]);
    }

    public function tipoEquipo()
    {
        return $this->belongsTo(TipoEquipo::class, 'tipo_equipo_id');
    }

    public function clasEquipo()
    {
        return $this->belongsTo(ClasEquipo::class, 'clas_equipo_id');
    }

    public function invConfigurations()
    {
        return $this->belongsToMany(InvConfiguration::class);
    }

    public function invItems()
    {
        return $this->hasMany(InvItem::class, 'inv_model_id');
    }
}
