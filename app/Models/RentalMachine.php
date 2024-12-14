<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class RentalMachine extends Model
{
    use HasFactory, SoftDeletes, FilterableModel;

    protected $fillable = [
        'picture',
        'serial',
        'model',
        'description',
        'hours',
        'comments',
        'status',
    ];

    protected $appends = ['pic'];

    public function pic(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->picture ? Storage::disk('s3')->url($this->picture) : null
        );
    }

    protected function defaultPathFolder(): Attribute
    {
        return Attribute::make(
            get: fn() => "Rentals/maquines/id_" . $this->serial,
        );
    }

    public function rentalPeriod()
    {
        return $this->hasMany(RentalPeriod::class, 'rental_machine_id');
    }

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['serial']);
    }
}
