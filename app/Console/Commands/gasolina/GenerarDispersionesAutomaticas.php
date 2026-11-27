<?php

namespace App\Console\Commands\Gasolina;

use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehiclesDeptoCargoA;
use App\Models\VehiclesDispersionAutomatica;
use App\Models\VehiclesDispersionGasolina;
use App\Models\VehiclesGasolinaBitacora;
use App\Models\VehiclesSucCargoA;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerarDispersionesAutomaticas extends Command
{
    protected $signature = 'gasolina:generar-dispersiones-automaticas';

    protected $description = 'Genera cada lunes a las 8:00 AM las solicitudes de dispersión de gasolina automáticas ya autorizadas (estatus Por dispersar).';

    public function handle(): int
    {
        $configs = VehiclesDispersionAutomatica::with('vehicle.empleados')->get();

        if ($configs->isEmpty()) {
            $this->info('No hay unidades configuradas en vehicles_dispersion_automatica.');
            return self::SUCCESS;
        }

        $estatusPorDispersar = Estatus::where('nombre', 'Por dispersar')
            ->where('tipo_estatus', 'servicio-unidad')
            ->first();

        if (!$estatusPorDispersar) {
            $this->error('No se encontró el estatus "Por dispersar" para servicio-unidad.');
            Log::error('gasolina:generar-dispersiones-automaticas - Estatus "Por dispersar" no encontrado.');
            return self::FAILURE;
        }

        // Usuario para bitácora (columna user_id es NOT NULL): Director Administrativo activo o fallback
        $bitacoraUserId = User::whereHas('empleado', function ($q) {
            $q->whereHas('puesto', fn($p) => $p->where('nombre', 'Coordinador de compras'))
                ->whereHas('estatus', fn($e) => $e->where('nombre', 'Activo'));
        })->value('id') ?? User::value('id');

        if (!$bitacoraUserId) {
            $this->error('No se encontró un usuario válido para registrar la bitácora.');
            return self::FAILURE;
        }

        $hoy = Carbon::now('America/Mexico_City')->toDateString();
        $creadas = 0;
        $omitidas = 0;

        foreach ($configs as $config) {
            $vehicleId = (int) $config->vehicle_id;

            $yaExisteHoy = VehiclesDispersionGasolina::where('vehicle_id', $vehicleId)
                ->where('is_automatica', true)
                ->whereDate('created_at', $hoy)
                ->exists();

            if ($yaExisteHoy) {
                $omitidas++;
                continue;
            }

            DB::transaction(function () use ($config, $vehicleId, $estatusPorDispersar, $bitacoraUserId) {
                $kmGas = (int) DB::table('vehicles_dispersion_gasolina')
                    ->where('vehicle_id', $vehicleId)
                    ->whereNotNull('km_evento')
                    ->max('km_evento');

                $kmServ = (int) DB::table('vehicles_detalles_servicio')
                    ->where('vehicle_id', $vehicleId)
                    ->whereNotNull('km_evento')
                    ->max('km_evento');

                $kmEvento = max($kmGas, $kmServ);

                $vehiculo = Vehicle::find($vehicleId);
                $resplonsableUnidad = $vehiculo?->responsable ?? $config->vehicle?->empleados?->first();
                $sucursalResponsable = $resplonsableUnidad?->sucursal->nombre;
                $deptoResponsable = $resplonsableUnidad?->departamento->nombre;
                $sucursalVehiculo = $vehiculo?->sucursal->nombre;
                $deptoVehiculo = $vehiculo?->departamento->nombre;

                $sucCargo = VehiclesSucCargoA::where('nombre', $sucursalResponsable)->value('id') ?? VehiclesSucCargoA::where('nombre', $sucursalVehiculo)->value('id');
                $deptoCargo = VehiclesDeptoCargoA::where('nombre', $deptoResponsable)->value('id') ?? VehiclesDeptoCargoA::where('nombre', $deptoVehiculo)->value('id');

                $lts = (int) $config->lts_cargar;

                $dispersion = VehiclesDispersionGasolina::create([
                    'vehicle_id' => $vehicleId,
                    'solicitado_por' => $resplonsableUnidad->id,
                    'registrado_por' => $bitacoraUserId,
                    'suc_cargo_id' => $sucCargo,
                    'depto_cargo_id' => $deptoCargo,
                    'lts_solicitados' => $lts,
                    'lts_dispersados' => $lts,
                    'km_evento' => $kmEvento ?? 0,
                    'fecha_requerida' => null,
                    'recorrido_url' => null,
                    'notas' => 'Dispersión automática semanal (Lunes 8:00 AM)',
                    'estatus_id' => $estatusPorDispersar->id,
                    'is_automatica' => true,
                ]);

                VehiclesGasolinaBitacora::create([
                    'dispersion_id' => $dispersion->id,
                    'estatus_id' => $estatusPorDispersar->id,
                    'user_id' => $bitacoraUserId,
                    'comentarios' => "Solicitud automática generada y autorizada ({$lts} Lts listos para dispersión)",
                ]);
            });

            $creadas++;
        }

        $this->info("Dispersiones automáticas procesadas. Creadas: {$creadas}, Omitidas (ya generadas hoy): {$omitidas}.");

        return self::SUCCESS;
    }
}
