<?php

namespace App\Models\Intranet;

use App\Models\Estatus;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    use FilterableModel;

    protected $fillable = [
       'amount',
       'comments',
       'feedback',
       'serial',
       'invoice',
       'order',
       'folio',
       'economic',
       'validated',
       'date',
       'cliente_id',
       'status_id',
       'referencia_id'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function status()
    {
        return $this->belongsTo(Estatus::class, 'status_id');
    }

    public function referencia()
    {
        return $this->belongsTo(Referencia::class, 'referencia_id');
    }
}
