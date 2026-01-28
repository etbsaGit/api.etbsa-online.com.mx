<?php

namespace App\Models\Intranet;

use App\Models\Sucursal;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvItem extends Model
{
    use HasFactory, FilterableModel;

    protected $fillable = [
        'inv_factory_id',
        'rd',
        'shipping_date',
        'shipping_status',
        'invoice',
        's_n',
        's_n_m',
        'e_n',
        'financing',
        'invoice_date',
        'purchase_cost',
        'is_paid',
        'paid_date',
        'gps',
        'notes',
        'inv_model_id',
        'sucursal_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($invItem) {

            $invItem->invItemDocs->each(function ($doc) {

                Storage::disk('s3')->delete($doc->path);

                $doc->delete();
            });
        });
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearchItem($query, $filters, ['rd', 'invoice', 's_n', 'e_n']);
    }

    public function invFactory()
    {
        return $this->belongsTo(InvFactory::class, 'inv_factory_id');
    }

    public function invModel()
    {
        return $this->belongsTo(InvModel::class, 'inv_model_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function invConfigurations()
    {
        return $this->belongsToMany(InvConfiguration::class);
    }

    public function invItemDocs()
    {
        return $this->hasMany(InvItemDoc::class, 'inv_item_id');
    }
}
