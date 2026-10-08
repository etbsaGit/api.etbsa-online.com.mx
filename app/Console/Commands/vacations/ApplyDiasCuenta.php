<?php

namespace App\Console\Commands\vacations;

use Illuminate\Console\Command;
use App\Services\VacationDiaCuentaService;

class ApplyDiasCuenta extends Command
{
    protected $signature = 'vacations:apply-dias-cuenta 
                            {--date= : Fecha a evaluar en formato YYYY-MM-DD (por defecto hoy)} 
                            {--empleado= : ID de empleado específico a evaluar} 
                            {--dry-run : Simula la ejecución sin guardar cambios en la base de datos}';

    protected $description = 'Registra solicitudes automáticas de vacaciones por días a cuenta a los empleados en su aniversario laboral.';

    public function handle(VacationDiaCuentaService $service)
    {
        $date = $this->option('date');
        $empleadoId = $this->option('empleado') ? (int) $this->option('empleado') : null;
        $dryRun = (bool) $this->option('dry-run');

        $this->info("====================================================");
        $this->info("Procesando días a cuenta de vacaciones");
        $this->info("Fecha: " . ($date ?: now()->toDateString()));
        if ($empleadoId) {
            $this->info("Empleado ID: {$empleadoId}");
        }
        if ($dryRun) {
            $this->warn("MODO SIMULACIÓN (DRY-RUN) - No se guardarán cambios");
        }
        $this->info("====================================================");

        $resumen = $service->aplicarPorAniversario($date, $dryRun, $empleadoId);

        $this->line("Empleados evaluados: " . $resumen['empleados_evaluados']);
        $this->line("Días a cuenta en periodo: " . $resumen['dias_en_periodo']);
        $this->newLine();

        if (empty($resumen['detalles'])) {
            $this->info("No hubo empleados con aniversario laboral en la fecha evaluada.");
            return Command::SUCCESS;
        }

        $totalCreados = 0;
        $totalOmitidos = 0;

        foreach ($resumen['detalles'] as $detalle) {
            $this->info("Empleado #{$detalle['empleado_id']}: {$detalle['nombre']} (Ingreso: {$detalle['fecha_de_ingreso']})");

            if (!empty($detalle['error'])) {
                $this->error("  ERROR: {$detalle['error']}");
                continue;
            }

            foreach ($detalle['creados'] as $c) {
                $totalCreados++;
                $this->line("  [OK] Creado: {$c['nombre']} ({$c['fecha']}) - Solicitud ID: {$c['vacation_id']} - Días pendientes: {$c['dias_pendientes']}");
            }

            foreach ($detalle['omitidos'] as $o) {
                $totalOmitidos++;
                $this->comment("  [OMITIDO] {$o['nombre']} ({$o['fecha']}): {$o['motivo']}");
            }

            $this->newLine();
        }

        $this->info("Resumen final: {$totalCreados} solicitudes " . ($dryRun ? 'simuladas' : 'creadas') . ", {$totalOmitidos} omitidas.");

        return Command::SUCCESS;
    }
}
