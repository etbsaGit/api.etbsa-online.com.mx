<?php

namespace App\Console\Commands\tracking;

use App\Models\Empleado;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendTrackingReminders extends Command
{
    protected $signature = 'tracking:send-tracking-reminders';

    protected $description = 'Envía notificaciones de seguimientos próximos';

    public function handle()
    {
        $hoy = now()->startOfDay();

        $manana = $hoy->copy()->addDay();

        $pasadoManana = $hoy->copy()->addDays(2);

        $this->info('Procesando seguimientos...');
        $this->newLine();

        /*
        |--------------------------------------------------------------------------
        | Empleados
        |--------------------------------------------------------------------------
        */

        $empleados = Empleado::with([
            'user',
            'tracking.ultimaActividad',
        ])->get();

        foreach ($empleados as $empleado) {

            /*
            |--------------------------------------------------------------------------
            | Trackings con última actividad
            |--------------------------------------------------------------------------
            */

            $trackings = $empleado->tracking
                ->filter(function ($tracking) use (
                    $hoy,
                    $pasadoManana
                ) {

                    if (!$tracking->ultimaActividad) {
                        return false;
                    }

                    if (!$tracking->ultimaActividad->date_next_tracking) {
                        return false;
                    }

                    $fecha = Carbon::parse(
                        $tracking->ultimaActividad->date_next_tracking
                    )->startOfDay();

                    /*
                    | Ignorar:
                    | - seguimientos atrasados
                    | - seguimientos posteriores a pasado mañana
                    */

                    return $fecha->between(
                        $hoy,
                        $pasadoManana
                    );
                });

            if ($trackings->isEmpty()) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Agrupar por fecha
            |--------------------------------------------------------------------------
            */

            $seguimientos = $trackings->groupBy(function ($tracking) {

                return Carbon::parse(
                    $tracking->ultimaActividad->date_next_tracking
                )->format('Y-m-d');

            });

            /*
            |--------------------------------------------------------------------------
            | Cantidades
            |--------------------------------------------------------------------------
            */

            $cantidadHoy = $seguimientos
                ->get(
                    $hoy->format('Y-m-d'),
                    collect()
                )
                ->count();

            $cantidadManana = $seguimientos
                ->get(
                    $manana->format('Y-m-d'),
                    collect()
                )
                ->count();

            $cantidadPasadoManana = $seguimientos
                ->get(
                    $pasadoManana->format('Y-m-d'),
                    collect()
                )
                ->count();

            /*
            |--------------------------------------------------------------------------
            | Construir mensaje
            |--------------------------------------------------------------------------
            */

            $lineas = [];

            if ($cantidadHoy > 0) {

                $lineas[] = 'Hoy tienes ' .
                    $cantidadHoy . ' ' .
                    ($cantidadHoy === 1
                        ? 'seguimiento'
                        : 'seguimientos');
            }

            if ($cantidadManana > 0) {

                $lineas[] = 'Mañana tienes ' .
                    $cantidadManana . ' ' .
                    ($cantidadManana === 1
                        ? 'seguimiento'
                        : 'seguimientos');
            }

            if ($cantidadPasadoManana > 0) {

                $lineas[] = 'Pasado mañana tienes ' .
                    $cantidadPasadoManana . ' ' .
                    ($cantidadPasadoManana === 1
                        ? 'seguimiento'
                        : 'seguimientos');
            }

            /*
            |--------------------------------------------------------------------------
            | Validar usuario
            |--------------------------------------------------------------------------
            */

            if (!$empleado->user) {

                $this->warn(
                    "Empleado {$empleado->nombreCompleto} " .
                    "no tiene usuario asociado."
                );

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Enviar notificación
            |--------------------------------------------------------------------------
            */

            NotificationService::send(
                user: $empleado->user,
                payload: [
                    'module' => 'tracking',
                    'type' => 'tracking',
                    'title' => '📋 Seguimientos pendientes',
                    'body' => implode("\n", $lineas),
                    'data' => [
                        'type' => 'tracking',
                    ],
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Mostrar en consola
            |--------------------------------------------------------------------------
            */

            $this->info(
                "Notificación enviada a: " .
                $empleado->nombreCompleto
            );

            foreach ($lineas as $linea) {
                $this->line("  → {$linea}");
            }

            $this->newLine();
        }

        $this->info('Proceso terminado.');

        return Command::SUCCESS;
    }
}