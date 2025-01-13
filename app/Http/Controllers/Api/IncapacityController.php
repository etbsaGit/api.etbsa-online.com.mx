<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Puesto;
use App\Models\Estatus;
use App\Models\Empleado;
use App\Models\Sucursal;
use App\Models\Incapacity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Incapacity\IncapacityRequest;
use App\Http\Requests\Incapacity\IncapacityPutRequest;

class IncapacityController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        $incapacities = Incapacity::filter($filters)
            ->with(['empleado', 'sucursal', 'puesto', 'estatus', 'children'])
            ->where('inicial', 1)
            ->orderBy('id', 'desc') // Ordenar por ID de forma descendente
            ->paginate(10);

        return $this->respond($incapacities);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(IncapacityRequest $request)
    {
        $incapacity = Incapacity::create($request->validated());

        return $this->respondCreated($incapacity);
    }

    /**
     * Display the specified resource.
     */
    public function show(Incapacity $incapacity)
    {
        return $this->respond($incapacity->load('empleado', 'puesto', 'sucursal', 'estatus', 'children'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(IncapacityPutRequest $request, Incapacity $incapacity)
    {
        // Actualiza el registro principal
        $incapacity->update($request->validated());

        // Procesa los registros en 'children'
        if ($request->has('children')) {
            foreach ($request->input('children') as $childData) {
                // Usa updateOrCreate para actualizar o crear registros en la base de datos
                $incapacity->children()->updateOrCreate(
                    ['id' => $childData['id'] ?? null], // Busca por 'id' si existe, o crea uno nuevo
                    $childData // Actualiza o crea con estos datos
                );
            }
        }

        return $this->respond($incapacity->load('children')); // Retorna el registro con los hijos cargados
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Incapacity $incapacity)
    {
        $incapacity->delete();

        return $this->respondSuccess();
    }

    public function getforms()
    {

        $data = [
            'empleados' => Empleado::where('estatus_id', 5)->orderBy('apellido_paterno')->get(),
            'puestos' => Puesto::all(),
            'sucursales' => Sucursal::all(),
            'estatuses' => Estatus::where('tipo_estatus', 'incapacity')->get()
        ];

        return $this->respond($data);
    }

    public function getIncapacityCalendar($date)
    {
        // Convierte la fecha proporcionada en un objeto Carbon para manejar el mes y año
        $parsedDate = Carbon::parse($date);
        $month = $parsedDate->month;
        $year = $parsedDate->year;

        // Consulta con Eloquent para filtrar los registros e incluir la relación
        $vacationDays = Incapacity::with('empleado') // Incluye la relación 'empleado'
            ->where(function ($query) use ($month, $year) {
                $query->where(function ($subQuery) use ($month, $year) {
                    $subQuery->whereMonth('fecha_inicio', $month)
                        ->whereYear('fecha_inicio', $year);
                })->orWhere(function ($subQuery) use ($month, $year) {
                    $subQuery->whereMonth('fecha_termino', $month)
                        ->whereYear('fecha_termino', $year);
                });
            })
            ->get();

        return $this->respond($vacationDays);
    }
}
