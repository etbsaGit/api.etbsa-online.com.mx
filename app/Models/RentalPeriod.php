<?php

namespace App\Models;

use App\Traits\FilterableModel;
use App\Models\Intranet\Cliente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RentalPeriod extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'folio',
        'start_date',
        'end_date',
        'billing_day',
        'rental_value',
        'comments',
        'document',
        'empleado_id',
        'cliente_id',
        'rental_machine_id'
    ];

    protected $appends = ['doc'];

    public function doc(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->document ? Storage::disk('s3')->url($this->document) : null
        );
    }

    protected function defaultPathFolder(): Attribute
    {
        return Attribute::make(
            get: fn() => "Rentals/periods/id_" . $this->folio,
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($rentalPeriod) {
            if ($rentalPeriod->document) {
                Storage::disk('s3')->delete($rentalPeriod->document);
            }
        });
    }

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['folio']);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function rentalMachine()
    {
        return $this->belongsTo(RentalMachine::class, 'rental_machine_id');
    }
}
