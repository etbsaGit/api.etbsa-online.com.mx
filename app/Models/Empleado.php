<?php

namespace App\Models;

use App\Models\User;
use App\Models\Linea;
use App\Models\Puesto;
use App\Models\Sucursal;
use App\Models\Expediente;
use App\Models\EstadoCivil;
use App\Models\Departamento;
use App\Models\TipoDeSangre;
use App\Models\Intranet\Sale;
use App\Traits\FilterableModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Empleado extends Model
{
    use HasFactory;

    use FilterableModel;

    protected $table = 'empleados';

    protected $fillable = [
        'fotografia',
        'nombre',
        'segundo_nombre',
        'apellido_paterno',
        'apellido_materno',
        'telefono',
        'telefono_institucional',
        'fecha_de_nacimiento',
        'curp',
        'rfc',
        'ine',
        'licencia_de_manejo',
        'nss',
        'fecha_de_ingreso',
        'hijos',
        'dependientes_economicos',
        'cedula_profesional',
        'matriz',
        'sueldo_base',
        'comision',
        'numero_exterior',
        'numero_interior',
        'calle',
        'colonia',
        'codigo_postal',
        'ciudad',
        'estado',
        'cuenta_bancaria',
        'correo_institucional',
        'usuario_x',
        'productividad',

        'escolaridad_id',
        'user_id',
        'puesto_id',
        'sucursal_id',
        'linea_id',
        'departamento_id',
        'estado_civil_id',
        'tipo_de_sangre_id',
        'jefe_directo_id',
        'notificar_id',
        'estatus_id',
        'vehicle_id',

        'descripcion_puesto',
        'carrera',

        'technician_id'
    ];

    protected $appends = ['picture', 'nombreCompleto', 'desempenoManoObra', 'apellidoCompleto', 'aniosVacaciones', 'prod', 'vacationPeriod'];

    public function picture(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->fotografia ? Storage::disk('s3')->url($this->fotografia) : null
        );
    }

    public function getVacationPeriodAttribute()
    {
        $fecha_de_ingreso = \Carbon\Carbon::parse($this->fecha_de_ingreso)->locale('es');
        $hoy = now();

        // Obtener el aniversario más reciente en el año actual
        $ultimo_aniversario = $fecha_de_ingreso->copy()->year($hoy->year);

        // Si el aniversario de este año aún no ha ocurrido, tomar el del año pasado
        if ($ultimo_aniversario->greaterThan($hoy)) {
            $ultimo_aniversario->subYear();
        }

        // El periodo inicia en el año siguiente al aniversario más reciente
        $anio_inicio = $ultimo_aniversario->year;
        $anio_fin = $anio_inicio + 1;

        // Obtener el mes de ingreso en español (abreviado)
        $mes_ingreso = $fecha_de_ingreso->translatedFormat('M');

        return strtolower("$mes_ingreso - $anio_inicio / $mes_ingreso - $anio_fin");
    }



    public function getNombreCompletoAttribute()
    {
        return $this->nombre . ' ' . $this->segundo_nombre . ' ' . $this->apellido_paterno . ' ' . $this->apellido_materno;
    }

    public function getApellidoCompletoAttribute()
    {
        return $this->apellido_paterno . ' ' . $this->apellido_materno . ' ' . $this->nombre . ' ' . $this->segundo_nombre;
    }

    public function getAniosVacacionesAttribute()
    {
        $fechaIngreso = \Carbon\Carbon::parse($this->fecha_de_ingreso);
        $hoy = \Carbon\Carbon::now();

        // Determina el año actual de aniversario
        $anioActual = $hoy->year;
        $mesDiaIngreso = $fechaIngreso->copy()->format('m-d');

        $inicioPeriodo = \Carbon\Carbon::createFromFormat('Y-m-d', "$anioActual-$mesDiaIngreso");
        if ($hoy->lt($inicioPeriodo)) {
            // Si aún no ha llegado al aniversario este año, restamos un año
            $inicioPeriodo->subYear();
        }
        $finPeriodo = $inicioPeriodo->copy()->addYear()->subDay();

        $aniosCumplidos = $fechaIngreso->diffInYears($inicioPeriodo);

        $dias_correspondientes = Antiguedad::where('id', $aniosCumplidos)->value('dias_correspondientes');

        $vacationDaysCount = VacationDay::where('empleado_id', $this->id)
            ->where('anios_cumplidos', $aniosCumplidos)
            ->where('validated', 1)
            ->whereBetween('fecha_inicio', [$inicioPeriodo, $finPeriodo])
            ->sum('dias_disfrute');

        return [
            'cumplidos' => $aniosCumplidos,
            'correspondientes' => $dias_correspondientes,
            'subtotal' => $dias_correspondientes - $vacationDaysCount,
            'periodo' => [
                'inicio' => $inicioPeriodo->toDateString(),
                'fin' => $finPeriodo->toDateString(),
            ],
        ];
    }


    public function getProdAttribute()
    {
        // Obtén el primer día del mes actual
        $startOfMonth = now()->startOfMonth();

        // Obtén el último día del mes actual
        $endOfMonth = now()->endOfMonth();

        // Obtén los invoices relacionados con el empleado
        // Filtra por el mes actual
        $invoices = $this->invoices()
            ->whereBetween('fecha', [$startOfMonth, $endOfMonth])
            ->get();

        // Suma las horas facturadas de cada invoice
        $totalHorasFacturadas = $invoices->sum('horas_facturadas');

        $resultados = [
            'horas' => $totalHorasFacturadas,
            'value' => $totalHorasFacturadas * 800,
        ];

        return $resultados;
    }

    // public function getDesempenoManoObraAttribute()
    // {
    //     // Obtén el mes y año más actuales de HorasTechnician
    //     $latestRecord = HorasTechnician::where('tecnico_id', $this->id)
    //         ->orderBy('anio', 'desc')
    //         ->orderBy('mes', 'desc')
    //         ->first();

    //     if (!$latestRecord) {
    //         return null; // No hay registros en HorasTechnician
    //     }

    //     // Obtén el mes y año más actuales
    //     $mes = $latestRecord->mes;
    //     $anio = $latestRecord->anio;

    //     // Encuentra el registro del mes y año más actuales para el empleado
    //     $currentMonthYearRecord = HorasTechnician::where('mes', $mes)
    //         ->where('anio', $anio)
    //         ->where('tecnico_id', $this->id) // Asegúrate de que `tecnico_id` coincida con el ID del empleado
    //         ->first();

    //     if (!$currentMonthYearRecord) {
    //         return null; // No hay registros para el mes y año más actuales
    //     }

    //     // Calcula el desempeño de mano de obra
    //     $facturadas = $currentMonthYearRecord->facturadas;
    //     $conIngreso = $currentMonthYearRecord->con_ingreso;

    //     if ($conIngreso == 0) {
    //         return null; // Evita la división por cero
    //     }

    //     return round(($facturadas / $conIngreso) * 100, 2);
    // }

    public function getDesempenoManoObraAttribute()
    {
        // Obtén el primer día del mes actual
        $startOfMonth = now()->startOfMonth();

        // Obtén el último día del mes actual
        $endOfMonth = now()->endOfMonth();

        // Obtén los invoices relacionados con el empleado
        // Filtra por el mes actual
        $invoices = $this->invoices()
            ->whereBetween('fecha', [$startOfMonth, $endOfMonth])
            ->get();

        // Suma las horas facturadas de cada invoice
        $totalHorasFacturadas = $invoices->sum('horas_facturadas');

        $techniciansLogs = $this->techniciansLog()
            ->whereHas('activityTechnician.estatus', function ($query) {
                $query->where('nombre', 'Horas con ingreso');
            })
            ->whereBetween('fecha', [$startOfMonth, $endOfMonth])
            ->get();

        $totalHoras = $techniciansLogs->reduce(function ($carry, $log) {
            $horaInicio = $log->hora_inicio;
            $horaTermino = $log->hora_termino;

            // Verifica que las fechas sean válidas
            if ($horaInicio && $horaTermino) {
                $start = \Carbon\Carbon::parse($horaInicio);
                $end = \Carbon\Carbon::parse($horaTermino);

                // Calcula la diferencia en horas
                $diffInHours = $start->diffInHours($end);

                // Acumula la diferencia
                $carry += $diffInHours;
            }

            return $carry;
        }, 0); // El valor inicial es 0

        // Calcular el porcentaje de horas facturadas sobre el total de horas
        $porcentajeHorasFacturadas = ($totalHoras > 0)
            ? ($totalHorasFacturadas / $totalHoras) * 100
            : 0;

        // Formatear el porcentaje a dos dígitos después del punto decimal
        $porcentajeHorasFacturadas = round($porcentajeHorasFacturadas, 2);

        // Devuelve el porcentaje
        return $porcentajeHorasFacturadas;
    }

    protected function defaultPathFolder(): Attribute
    {
        return Attribute::make(
            get: fn() => "empleados/id_" . $this->id . "/foto_de_perfil",
        );
    }

    // -Scope-
    public function scopeFilter(Builder $query, array $filters)
    {
        return $this->scopeFilterSearch($query, $filters, ['nombre', 'segundo_nombre', 'apellido_paterno', 'apellido_materno', 'curp', 'rfc', 'telefono_institucional']);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function escolaridad()
    {
        return $this->belongsTo(Escolaridad::class, 'escolaridad_id');
    }

    public function puesto()
    {
        return $this->belongsTo(Puesto::class, 'puesto_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function linea()
    {
        return $this->belongsTo(Linea::class, 'linea_id');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function estado_civil()
    {
        return $this->belongsTo(EstadoCivil::class, 'estado_civil_id');
    }

    public function tipo_de_sangre()
    {
        return $this->belongsTo(TipoDeSangre::class, 'tipo_de_sangre_id');
    }

    public function archivable()
    {
        return $this->morphMany(Expediente::class, 'archivable');
    }

    public function jefe_directo()
    {
        return $this->belongsTo(Empleado::class, 'jefe_directo_id');
    }

    public function estatus()
    {
        return $this->belongsTo(Estatus::class, 'estatus_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'empleado_id');
    }

    public function activity()
    {
        return $this->hasMany(Activity::class, 'empleado_id');
    }

    public function termination()
    {
        return $this->hasOne(Termination::class);
    }

    // -----------------------------------------------------------------

    public function empleado()
    {
        return $this->hasMany(Empleado::class, 'jefe_directo_id');
    }

    public function career()
    {
        return $this->hasMany(Career::class, 'empleado_id');
    }

    // ----------------------------------------------------------------------------------

    public function horasFacturadas()
    {
        return $this->hasMany(HorasTechnician::class, 'tecnico_id');
    }

    public function invoices()
    {
        return $this->hasMany(TechniciansInvoice::class, 'tecnico_id');
    }

    public function techniciansLog()
    {
        return $this->hasMany(TechniciansLog::class, 'tecnico_id');
    }


    //----------------------------------Qualification---------------------------------------------------------
    public function qualification()
    {
        return $this->belongsToMany(Qualification::class, 'p_empleado_qualification', 'empleado_id', 'qualification_id');
    }

    public function technician()
    {
        return $this->belongsTo(Technician::class, 'technician_id');
    }

    public function bay()
    {
        return $this->hasMany(Bay::class, 'tecnico_id');
    }

    public function workOrder()
    {
        return $this->hasMany(WorkOrder::class, 'tecnico_id');
    }

    //----------------------------------SkillRating---------------------------------------------------------
    public function skillRating()
    {
        return $this->hasMany(SkillRating::class, 'empleado_id');
    }

    // ---------------------------------Intranet.sale---------------------------------------------------------

    public function sales()
    {
        return $this->hasMany(Sale::class, 'empleado_id');
    }

    // ---------------------------------Rentals.periods---------------------------------------------------------

    public function rentalPeriod()
    {
        return $this->hasMany(RentalPeriod::class, 'empleado_id');
    }

    // ---------------------------------VacationDay---------------------------------------------------------

    public function vacationDays()
    {
        return $this->hasMany(VacationDay::class, 'empleado_id');
    }

    public function cubre()
    {
        return $this->hasMany(VacationDay::class, 'cubre');
    }

    // ---------------------------------Visits---------------------------------------------------------

    public function visits()
    {
        return $this->hasMany(Visit::class, 'empleado_id');
    }

    public function prospects()
    {
        return $this->hasMany(Prospect::class, 'empleado_id');
    }

    public function cliente()
    {
        return $this->hasMany(Prospect::class, 'vendedor_id');
    }

    // ---------------------------------Notificar---------------------------------------------------------

    public function notificar()
    {
        return $this->belongsTo(Empleado::class, 'notificar_id');
    }

    public function destinatario()
    {
        return $this->hasMany(Empleado::class, 'notificar_id');
    }

    // ---------------------------------Vehicle---------------------------------------------------------

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    // ---------------------------------Contacto de emergencia---------------------------------------------------------


    public function empleadosContact()
    {
        return $this->hasMany(EmpleadosContact::class, 'empleado_id');
    }

    // ---------------------------------scope---------------------------------------------------------

    public function scopeFiltertwo(Builder $query, array $filters)
    {
        foreach ($filters as $key => $values) {
            if ($values !== null) {
                if (is_array($values)) {
                    $query->where(function ($query) use ($key, $values) {
                        foreach ($values as $value) {
                            $query->orWhere($key, $value);
                        }
                    });
                } else {
                    $query->Where($key, $values);
                }
            }
        }
    }
}
