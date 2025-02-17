<?php

namespace App\Models;

use App\Traits\FilterableModel;
use App\Models\Intranet\Cultivo;
use App\Models\Intranet\TipoCultivo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProspectCultivo extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'prospect_cultivos';

    protected $fillable = [
        'prospect_id',
        'cultivo_id',
        'tipo_cultivo_id'
    ];

    public function prospect()
    {
        return $this->belongsTo(Prospect::class, 'prospect_id');
    }

    public function cultivo()
    {
        return $this->belongsTo(Cultivo::class, 'cultivo_id');
    }

    public function tipoCultivo()
    {
        return $this->belongsTo(TipoCultivo::class, 'tipo_cultivo_id');
    }
}
