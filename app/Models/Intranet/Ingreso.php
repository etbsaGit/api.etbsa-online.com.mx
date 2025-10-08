<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ingreso extends Model
{
    use HasFactory;

    protected $fillable = [
        'monto',
        'tipo',
        'year',
        'months',
        'cliente_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($ingreso) {

            $ingreso->ingresoDocs->each(function ($doc) {

                Storage::disk('s3')->delete($doc->path);

                $doc->delete();
            });
        });
    }

    protected $appends = ['total'];

    public function getTotalAttribute()
    {
        return $this->monto * $this->months;
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function ingresoDocs()
    {
        return $this->hasMany(IngresoDoc::class, 'ingreso_id');
    }
}
