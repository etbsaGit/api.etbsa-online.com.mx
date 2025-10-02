<?php

namespace App\Models\Intranet;

use App\Models\Caja\CajaTransaccion;
use App\Models\CreditoDeclaracion;
use App\Models\RentalPeriod;
use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cliente extends Model
{
    use HasFactory;

    use FilterableModel;

    protected $table = 'clientes';

    protected $fillable = [
        'equip',
        'nombre',
        'tipo',
        'rfc',
        'curp',
        'telefono',
        'telefono_casa',
        'email',
        'state_entity_id',
        'town_id',
        'colonia',
        'calle',
        'codigo_postal',
        'classification_id',
        'segmentation_id',
        'tactic_id',
        'construction_classification_id'
    ];

    protected $appends = ['currentClassTech', 'hectareasConectadas'];

    // app/Models/Cliente.php

    public function getHectareasConectadasAttribute()
    {
        // Obtener relaciones (cargadas o no)
        $clienteTechnology = $this->relationLoaded('clienteTechnology')
            ? $this->clienteTechnology
            : $this->clienteTechnology()->get();

        $distribuciones = $this->relationLoaded('distribucion')
            ? $this->distribucion
            : $this->distribucion()->get();

        // Sumar valores
        $hectareasConectadas = $clienteTechnology->sum('hectareas');
        $hectareasRentadas   = $distribuciones->sum('hectareas_rentadas');
        $hectareasPropias    = $distribuciones->sum('hectareas_propias');

        // Calcular hectáreas sin conectar
        $totalDistribuidas   = $hectareasPropias + $hectareasRentadas;
        // $hectareasSinConectar = max(0, $totalDistribuidas - $hectareasConectadas);
        $hectareasSinConectar = $totalDistribuidas - $hectareasConectadas;


        return (object) [
            'hectareas_conectadas'   => $hectareasConectadas,
            'hectareas_rentadas'     => $hectareasRentadas,
            'hectareas_propias'      => $hectareasPropias,
            'hectareas_sin_conectar' => $hectareasSinConectar,
        ];
    }

    // Cliente.php (Modelo)
    public function getCurrentClassTechAttribute()
    {
        // Define el orden de los niveles
        $levels = ['Baja', 'Media', 'Alta', 'Experto'];

        // Verifica si hay capacidades tecnológicas asociadas
        if ($this->technologicalCapabilities->isEmpty()) {
            return null;
        }

        // Encuentra el nivel más alto asociado al cliente
        $highestLevelCapability = $this->technologicalCapabilities
            ->sortBy(function ($capability) use ($levels) {
                // Ordena por el índice del nivel en el array
                return array_search($capability->level, $levels);
            })
            ->last(); // Obtiene el último elemento después de ordenar

        // Retorna el nombre del nivel más alto
        return $highestLevelCapability ? $highestLevelCapability->level : null;
    }

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre', 'telefono', 'rfc']);
    }

    public function stateEntity()
    {
        return $this->belongsTo(StateEntity::class, 'state_entity_id');
    }

    public function town()
    {
        return $this->belongsTo(Town::class, 'town_id');
    }

    public function classification()
    {
        return $this->belongsTo(Classification::class, 'classification_id');
    }

    public function segmentation()
    {
        return $this->belongsTo(Segmentation::class, 'segmentation_id');
    }

    public function tactic()
    {
        return $this->belongsTo(Tactic::class, 'tactic_id');
    }

    public function constructionClassification()
    {
        return $this->belongsTo(ConstructionClassification::class, 'construction_classification_id');
    }

    public function referencia()
    {
        return $this->hasMany(Referencia::class, 'cliente_id');
    }

    public function referenciaComercial()
    {
        return $this->hasMany(ReferenciaComercial::class, 'cliente_id');
    }

    public function representante()
    {
        return $this->hasOne(Representante::class, 'cliente_id');
    }

    public function machine()
    {
        return $this->hasMany(Machine::class, 'cliente_id');
    }

    public function clienteTechnology()
    {
        return $this->hasMany(ClienteTechnology::class, 'cliente_id');
    }

    public function distribucion()
    {
        return $this->hasMany(Distribucion::class, 'cliente_id');
    }

    public function clienteCultivo()
    {
        return $this->hasMany(ClienteCultivo::class, 'cliente_id');
    }

    public function clienteRiego()
    {
        return $this->hasMany(ClienteRiego::class, 'cliente_id');
    }

    public function clienteAbastecimiento()
    {
        return $this->hasMany(ClienteAbastecimiento::class, 'cliente_id');
    }

    public function clienteDoc()
    {
        return $this->hasMany(ClientesDoc::class, 'cliente_id');
    }

    public function technologicalCapabilities()
    {
        return $this->belongsToMany(TechnologicalCapability::class, 'p_clientes_technological_capabilities');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'cliente_id');
    }

    public function rentalPeriod()
    {
        return $this->hasMany(RentalPeriod::class, 'cliente_id');
    }

    public function declaraciones()
    {
        return $this->hasMany(CreditoDeclaracion::class, 'cliente_id');
    }

    public function invercionesAgricolas()
    {
        return $this->hasMany(AgricolaInversion::class, 'cliente_id');
    }

    public function invercionesGanaderas()
    {
        return $this->hasMany(GanaderaInversion::class, 'cliente_id');
    }

    public function fincas()
    {
        return $this->hasMany(Finca::class, 'cliente_id');
    }

    public function analiticas()
    {
        return $this->hasMany(Analitica::class, 'cliente_id');
    }

    public function ingresos()
    {
        return $this->hasMany(Ingreso::class, 'cliente_id');
    }
}
