<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\VacationDiaCuenta\PutRequest;
use App\Http\Requests\VacationDiaCuenta\StoreRequest;
use App\Models\VacationDiaCuenta;

class VacationDiaCuentaController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $year = $request->input('year'); // Cambiado a una única variable en lugar de un array

        // Validar que se reciba exactamente un year
        if (!is_numeric($year) || strlen($year) !== 4) {
            return $this->respond(['error' => 'Debes enviar un year válido en formato YYYY'], 400);
        }

        $dias_cuenta = VacationDiaCuenta::whereYear('fecha', $year)->get();

        return $this->respond($dias_cuenta);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request, \App\Services\VacationDiaCuentaService $service)
    {
        try {
            $dia_cuenta = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $service) {
                $dia = VacationDiaCuenta::create($request->validated());
                $service->aplicarDia($dia);
                return $dia;
            });

            return $this->respondCreated($dia_cuenta);
        } catch (\Throwable $e) {
            return $this->respond(['error' => 'Error al registrar el día a cuenta: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(VacationDiaCuenta $vacationDiaCuenta)
    {
        return $this->respond($vacationDiaCuenta);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, VacationDiaCuenta $vacationDiaCuenta, \App\Services\VacationDiaCuentaService $service)
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $vacationDiaCuenta, $service) {
                $validated = $request->validated();
                $fechaCambio = isset($validated['fecha']) && $validated['fecha'] !== $vacationDiaCuenta->fecha;

                if ($fechaCambio) {
                    $service->retirarDia($vacationDiaCuenta);
                    $vacationDiaCuenta->update($validated);
                    $service->aplicarDia($vacationDiaCuenta);
                } else {
                    $nombreCambio = isset($validated['nombre']) && $validated['nombre'] !== $vacationDiaCuenta->nombre;
                    $vacationDiaCuenta->update($validated);
                    if ($nombreCambio) {
                        \App\Models\VacationDay::where('vacation_dia_cuenta_id', $vacationDiaCuenta->id)
                            ->whereRaw('created_at = updated_at')
                            ->update(['comentarios' => "Día a cuenta: {$vacationDiaCuenta->nombre} (automático)"]);
                    }
                }
            });

            return $this->respond($vacationDiaCuenta->fresh());
        } catch (\Throwable $e) {
            return $this->respond(['error' => 'Error al actualizar el día a cuenta: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VacationDiaCuenta $vacationDiaCuenta, \App\Services\VacationDiaCuentaService $service)
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($vacationDiaCuenta, $service) {
                $service->retirarDia($vacationDiaCuenta);
                $vacationDiaCuenta->delete();
            });

            return $this->respondSuccess();
        } catch (\Throwable $e) {
            return $this->respond(['error' => 'Error al eliminar el registro: ' . $e->getMessage()], 500);
        }
    }

    public function getFecha($year)
    {
        $fechas = VacationDiaCuenta::whereYear('fecha', $year)
            ->orWhereYear('fecha', $year - 1)
            ->orWhereYear('fecha', $year + 1)
            ->pluck('fecha')
            ->toArray();

        return $this->respond($fechas);
    }
}
