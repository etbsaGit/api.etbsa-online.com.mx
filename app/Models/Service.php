<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory, FilterableModel;


    protected $fillable = [
        'description',
        'status',
        'km',
        'vehicle_id',
        'empleado_id',
        'estatus_id',
        'feedback',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($service) {

            $service->archives->each(function ($archive) {

                Storage::disk('s3')->delete($archive->path);

                $archive->delete();
            });
        });
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['description', 'km']);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }

    public function archives()
    {
        return $this->hasMany(ServiceArchive::class, 'service_id');
    }
}
