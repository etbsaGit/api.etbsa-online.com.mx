<?php

namespace App\Models;

use Carbon\Carbon;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehiclesDocumentos extends Model
{
    use HasFactory, FilterableModel;

    protected $table = 'vehicles_documentos';

    protected $fillable = [
        'vehicle_id',
        'tipo_documento',
        'folio_o_poliza',
        'detalle_extra',
        'fecha_emision',
        'fecha_vencimiento',
        'archivo_path',
        'es_vigente',
        'observaciones',
        'registered_by',
    ];

    protected $casts = [
        'es_vigente' => 'boolean',
    ];

    protected $appends = [
        'archivo_url',
        'dias_restantes',
        'estatus_vigencia',
    ];

    public function getArchivoUrlAttribute(): ?string
    {
        if (empty($this->archivo_path)) {
            return null;
        }

        if (str_starts_with($this->archivo_path, 'http://') || str_starts_with($this->archivo_path, 'https://')) {
            return $this->archivo_path;
        }

        try {
            return Storage::disk('s3')->url($this->archivo_path);
        } catch (\Throwable $e) {
            return $this->archivo_path;
        }
    }

    public function getDiasRestantesAttribute(): ?int
    {
        if (empty($this->fecha_vencimiento)) {
            return null;
        }

        try {
            $hoy = Carbon::now()->startOfDay();
            $vence = Carbon::parse($this->fecha_vencimiento)->startOfDay();
            return (int) $hoy->diffInDays($vence, false);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Calcula automáticamente el estado del semáforo de vigencia:
     * - 'historico': documento reemplazado (es_vigente = false)
     * - 'vigente': sin fecha de vencimiento (permanente) o faltan > 30 días
     * - 'por_vencer': faltan entre 0 y 30 días
     * - 'vencido': la fecha de vencimiento ya pasó (< 0 días)
     */
    public function getEstatusVigenciaAttribute(): string
    {
        if (!$this->es_vigente) {
            return 'historico';
        }

        if (empty($this->fecha_vencimiento)) {
            return 'vigente';
        }

        $dias = $this->dias_restantes;
        if ($dias === null) {
            return 'vigente';
        }

        if ($dias < 0) {
            return 'vencido';
        }

        if ($dias <= 30) {
            return 'por_vencer';
        }

        return 'vigente';
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}
