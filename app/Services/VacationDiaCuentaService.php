<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Festivo;
use App\Models\Empleado;
use App\Models\VacationDay;
use App\Models\VacationDiaCuenta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VacationDiaCuentaService
{
    /**
     * Cache festivos para cálculos de días hábiles.
     */
    protected ?array $festivos = null;

    public function getFestivos(): array
    {
        if ($this->festivos === null) {
            $this->festivos = Festivo::pluck('fecha')
                ->map(fn($d) => Carbon::parse($d)->toDateString())
                ->toArray();
        }
        return $this->festivos;
    }

    /**
     * Determina si una fecha es día hábil (no domingo y no festivo).
     */
    public function esDiaHabil(Carbon $fecha): bool
    {
        if ($fecha->dayOfWeek === Carbon::SUNDAY) {
            return false;
        }
        return !in_array($fecha->toDateString(), $this->getFestivos());
    }

    /**
     * Calcula la fecha de regreso al trabajo (siguiente día hábil después de la fecha de término).
     */
    public function calcularFechaRegreso(Carbon $fechaTermino): string
    {
        $current = $fechaTermino->copy();
        do {
            $current->addDay();
            if ($this->esDiaHabil($current)) {
                break;
            }
        } while (true);

        return $current->toDateString();
    }

    /**
     * Registra un día a cuenta específico para un empleado, aplicando todas las validaciones
     * e idempotencia requeridas.
     *
     * Retorna el VacationDay creado si fue exitoso, o null con la razón en $motivoOmitido.
     */
    public function registrarDia(Empleado $empleado, VacationDiaCuenta $dia, bool $dryRun = false, ?string &$motivoOmitido = null): ?VacationDay
    {
        $fechaDia = Carbon::parse($dia->fecha)->startOfDay();

        // 1. Validar que el día no caiga en domingo ni festivo
        if (!$this->esDiaHabil($fechaDia)) {
            $motivoOmitido = "La fecha {$dia->fecha} es domingo o día festivo oficial";
            Log::info("VacationDiaCuenta: Empleado {$empleado->id} ({$empleado->nombreCompleto}) omitido para día {$dia->nombre} ({$dia->fecha}): {$motivoOmitido}");
            return null;
        }

        // 2. Idempotencia: Verificar si ya existe un VacationDay asociado a este vacation_dia_cuenta_id
        $yaAsignado = VacationDay::where('empleado_id', $empleado->id)
            ->where('vacation_dia_cuenta_id', $dia->id)
            ->exists();

        if ($yaAsignado) {
            $motivoOmitido = "Ya tiene registrada la solicitud para este día a cuenta (id {$dia->id})";
            return null;
        }

        // 3. Verificar si el empleado ya cuenta con alguna solicitud activa (no rechazada y no eliminada) que cubra esa fecha
        $fechaStr = $fechaDia->toDateString();
        $solicitudExistente = VacationDay::where('empleado_id', $empleado->id)
            ->where('fecha_inicio', '<=', $fechaStr)
            ->where('fecha_termino', '>=', $fechaStr)
            ->where(function ($q) {
                // validated = 0 es rechazada; consideramos cubierto si está pendiente (null) o aceptada (1)
                $q->whereNull('validated')->orWhere('validated', '!=', 0);
            })
            ->exists();

        if ($solicitudExistente) {
            $motivoOmitido = "Ya tiene una solicitud de vacaciones que cubre la fecha {$fechaStr}";
            Log::info("VacationDiaCuenta: Empleado {$empleado->id} ({$empleado->nombreCompleto}) omitido para día {$dia->nombre} ({$dia->fecha}): {$motivoOmitido}");
            return null;
        }

        // 4. Evaluar derechos y saldo de vacaciones
        // Forzamos la recarga del empleado para recalcular aniosVacaciones con base en la BD actualizada
        $empleadoActual = $empleado->fresh(['vehicle']);
        $a = $empleadoActual->aniosVacaciones;

        if (($a['cumplidos'] ?? 0) < 1 || empty($a['correspondientes'])) {
            $motivoOmitido = "No tiene derecho a vacaciones aún (años cumplidos: " . ($a['cumplidos'] ?? 0) . ")";
            Log::info("VacationDiaCuenta: Empleado {$empleado->id} ({$empleado->nombreCompleto}) omitido para día {$dia->nombre} ({$dia->fecha}): {$motivoOmitido}");
            return null;
        }

        // Regla: "El saldo negativo no se registra"
        // Si el saldo disponible es menor a 1 día, no se puede descontar
        if (($a['subtotal'] ?? 0) < 1) {
            $motivoOmitido = "Saldo insuficiente (disponible: " . ($a['subtotal'] ?? 0) . " días)";
            Log::info("VacationDiaCuenta: Empleado {$empleado->id} ({$empleado->nombreCompleto}) omitido para día {$dia->nombre} ({$dia->fecha}): {$motivoOmitido}");
            return null;
        }

        $fechaRegreso = $this->calcularFechaRegreso($fechaDia);
        $diasPendientes = $a['subtotal'] - 1;

        $datos = [
            'empleado_id' => $empleadoActual->id,
            'sucursal_id' => $empleadoActual->sucursal_id,
            'puesto_id' => $empleadoActual->puesto_id,
            'departamento_id' => $empleadoActual->departamento_id,
            'vehiculo_utilitario' => $empleadoActual->vehicle?->placas,
            'periodo_correspondiente' => $empleadoActual->vacationPeriod,
            'anios_cumplidos' => $a['cumplidos'],
            'dias_periodo' => $a['correspondientes'],
            'subtotal_dias' => $a['subtotal'],
            'dias_disfrute' => 1,
            'dias_pendientes' => $diasPendientes,
            'fecha_inicio' => $fechaStr,
            'fecha_termino' => $fechaStr,
            'fecha_regreso' => $fechaRegreso,
            'validated' => 1,
            'comentarios' => null,
            'vacation_dia_cuenta_id' => $dia->id,
            'cubre' => null,
            'created_by' => null,
            'validate_by' => null,
        ];

        if ($dryRun) {
            return new VacationDay($datos);
        }

        $vacation = VacationDay::create($datos);

        Log::info("VacationDiaCuenta: Creada solicitud id {$vacation->id} para empleado {$empleadoActual->id} ({$empleadoActual->nombreCompleto}) por día '{$dia->nombre}' ({$dia->fecha})");

        return $vacation;
    }

    /**
     * Fase 1: Aplica días a cuenta a los empleados que cumplen aniversario laboral en la fecha indicada.
     * Retorna un resumen con los resultados.
     */
    public function aplicarPorAniversario(?string $fecha = null, bool $dryRun = false, ?int $empleadoId = null): array
    {
        $targetDate = $fecha ? Carbon::parse($fecha)->startOfDay() : now()->startOfDay();

        // En caso de simular una fecha, establecemos TestNow para que los accessors
        // calculen correctamente aniosVacaciones y vacationPeriod como si fuera esa fecha
        if ($fecha) {
            Carbon::setTestNow($targetDate->copy()->setTime(7, 0, 0));
        }

        try {
            $query = Empleado::where('estatus_id', 5)
                ->whereYear('fecha_de_ingreso', '<', $targetDate->year);

            if ($empleadoId) {
                $query->where('id', $empleadoId);
            }

            // Filtrar empleados que cumplen aniversario hoy:
            // 1. Mismo mes y día
            // 2. Si hoy es 1 de marzo en año NO bisiesto, incluir a los que ingresaron 29 de febrero
            $esPrimeroMarzoNoBisiesto = ($targetDate->month === 3 && $targetDate->day === 1 && !$targetDate->isLeapYear());

            if ($esPrimeroMarzoNoBisiesto) {
                $query->where(function ($q) use ($targetDate) {
                    $q->whereRaw('DATE_FORMAT(fecha_de_ingreso, "%m-%d") = ?', [$targetDate->format('m-d')])
                        ->orWhereRaw('DATE_FORMAT(fecha_de_ingreso, "%m-%d") = "02-29"');
                });
            } else {
                $query->whereRaw('DATE_FORMAT(fecha_de_ingreso, "%m-%d") = ?', [$targetDate->format('m-d')]);
            }

            $empleados = $query->with(['vehicle'])->get();

            // Rango de fechas del nuevo periodo que comienza hoy:
            // Desde hoy hasta hoy + 1 año - 1 día
            $ini = $targetDate->toDateString();
            $fin = $targetDate->copy()->addYear()->subDay()->toDateString();

            $diasCuenta = VacationDiaCuenta::whereBetween('fecha', [$ini, $fin])
                ->orderBy('fecha')
                ->get();

            $resumen = [
                'fecha' => $targetDate->toDateString(),
                'dry_run' => $dryRun,
                'empleados_evaluados' => $empleados->count(),
                'dias_en_periodo' => $diasCuenta->count(),
                'detalles' => [],
            ];

            foreach ($empleados as $empleado) {
                $empleadoResumen = [
                    'empleado_id' => $empleado->id,
                    'nombre' => $empleado->nombreCompleto,
                    'fecha_de_ingreso' => $empleado->fecha_de_ingreso,
                    'creados' => [],
                    'omitidos' => [],
                ];

                if (!$dryRun) {
                    DB::beginTransaction();
                }

                try {
                    foreach ($diasCuenta as $dia) {
                        $motivo = null;
                        $vacation = $this->registrarDia($empleado, $dia, $dryRun, $motivo);

                        if ($vacation) {
                            $empleadoResumen['creados'][] = [
                                'dia_cuenta_id' => $dia->id,
                                'nombre' => $dia->nombre,
                                'fecha' => $dia->fecha,
                                'vacation_id' => $vacation->id ?? 'dry-run',
                                'dias_pendientes' => $vacation->dias_pendientes,
                            ];
                        } else {
                            $empleadoResumen['omitidos'][] = [
                                'dia_cuenta_id' => $dia->id,
                                'nombre' => $dia->nombre,
                                'fecha' => $dia->fecha,
                                'motivo' => $motivo,
                            ];
                        }
                    }

                    if (!$dryRun) {
                        DB::commit();
                    }
                } catch (\Throwable $e) {
                    if (!$dryRun) {
                        DB::rollBack();
                    }
                    Log::error("VacationDiaCuenta: Error procesando empleado {$empleado->id}: " . $e->getMessage(), [
                        'trace' => $e->getTraceAsString()
                    ]);
                    $empleadoResumen['error'] = $e->getMessage();
                }

                $resumen['detalles'][] = $empleadoResumen;
            }

            return $resumen;
        } finally {
            if ($fecha) {
                Carbon::setTestNow();
            }
        }
    }

    /**
     * Fase 2: Aplica un día a cuenta específico a todos los empleados activos cuyo periodo
     * vigente incluya la fecha de dicho día.
     */
    public function aplicarDia(VacationDiaCuenta $dia): array
    {
        $fechaDia = Carbon::parse($dia->fecha)->startOfDay();
        $empleados = Empleado::where('estatus_id', 5)->with('vehicle')->get();

        $stats = [
            'dia' => $dia->nombre,
            'fecha' => $dia->fecha,
            'evaluados' => $empleados->count(),
            'creados' => 0,
            'omitidos' => 0,
            'fuera_de_periodo' => 0,
        ];

        foreach ($empleados as $empleado) {
            $a = $empleado->aniosVacaciones;

            // Omitir si no tiene derecho o periodo aún
            if (($a['cumplidos'] ?? 0) < 1 || empty($a['correspondientes']) || empty($a['periodo']['inicio'])) {
                $stats['fuera_de_periodo']++;
                continue;
            }

            $inicioPeriodo = Carbon::parse($a['periodo']['inicio'])->startOfDay();
            $finPeriodo = Carbon::parse($a['periodo']['fin'])->endOfDay();

            // Solo aplicar si la fecha cae dentro de su periodo vigente
            if (!$fechaDia->between($inicioPeriodo, $finPeriodo)) {
                $stats['fuera_de_periodo']++;
                continue;
            }

            $motivo = null;
            $vacation = $this->registrarDia($empleado, $dia, false, $motivo);

            if ($vacation) {
                $stats['creados']++;
            } else {
                $stats['omitidos']++;
            }
        }

        Log::info("VacationDiaCuenta: aplicarDia terminado para {$dia->nombre} ({$dia->fecha})", $stats);

        return $stats;
    }

    /**
     * Fase 2: Retira las solicitudes generadas automáticamente por este día a cuenta que no hayan
     * sido modificadas por RRHH.
     */
    public function retirarDia(VacationDiaCuenta $dia): int
    {
        $vacaciones = VacationDay::where('vacation_dia_cuenta_id', $dia->id)
            ->whereNull('deleted_at')
            ->get();

        $eliminadas = 0;

        foreach ($vacaciones as $v) {
            // Si nadie la ha modificado (created_at == updated_at y validated == 1), la eliminamos
            $noModificada = ($v->created_at && $v->updated_at && $v->created_at->eq($v->updated_at) && $v->validated === 1);

            if ($noModificada) {
                $v->delete();
                $eliminadas++;
            } else {
                // Si fue modificada manualmente por RRHH, no la eliminamos, pero desvinculamos el dia a cuenta
                $v->update(['vacation_dia_cuenta_id' => null]);
                Log::info("VacationDiaCuenta: Solicitud id {$v->id} para empleado {$v->empleado_id} fue modificada previamente por RRHH; se desvinculó de {$dia->nombre} sin eliminar.");
            }
        }

        Log::info("VacationDiaCuenta: retirarDia completado para {$dia->nombre} ({$dia->fecha}); {$eliminadas} solicitudes eliminadas.");

        return $eliminadas;
    }
}
