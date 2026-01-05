<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Models\Linea;
use App\Models\Empleado;
use App\Models\Sucursal;
use App\Models\Technician;
use Illuminate\Http\Request;
use App\Models\LineaTechnician;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Technician\PutRequest;
use App\Http\Requests\Technician\StoreRequest;

class TechnicianController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $technicians = Technician::with(['lineaTechnician.linea'])
            ->orderBy('level', 'asc')
            ->get();

        $lineas = Linea::whereIn('nombre', ['Agricola', 'Construccion'])->get();

        return $this->respond([
            'technicians' => $technicians,
            'lineas' => $lineas,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $technician = Technician::create($request->validated());

        $lineas = $request->lineas;

        foreach ($lineas as $linea) {
            LineaTechnician::create([
                'technician_id' => $technician->id,
                'linea_id' => $linea
            ]);
        }

        return $this->respond($technician);
    }

    /**
     * Display the specified resource.
     */
    public function show(Technician $technician)
    {
        return $this->respond($technician);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, Technician $technician)
    {

        $lineasNuevas = $request->lineas;

        $lineasActuales = $technician->lineaTechnician->pluck('linea_id')->toArray();

        $lineasAgregar = array_diff($lineasNuevas, $lineasActuales);
        $lineasEliminar = array_diff($lineasActuales, $lineasNuevas);

        foreach ($lineasEliminar as $lineaId) {
            $lineaTechnician = LineaTechnician::where('linea_id', $lineaId)
                ->where('technician_id', $technician->id)
                ->first();
            if ($lineaTechnician) {
                $lineaTechnician->delete();
            }
        }

        foreach ($lineasAgregar as $lineaId) {
            LineaTechnician::create([
                'linea_id' => $lineaId,
                'technician_id' => $technician->id,
            ]);
        }

        $technician->update($request->validated());

        return $this->respond($technician);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Technician $technician)
    {
        $technician->delete();
        return $this->respond("ok");
    }

    public function getAll()
    {
        $lineaAgricola = Linea::where('nombre', 'Agricola')->first();

        $calificacionesAgricolas = $lineaAgricola->lineaTechnician()
            ->where('linea_id', $lineaAgricola->id)
            ->with(['technician', 'qualification'])
            ->get();
        $calificacionesAgricolas = $calificacionesAgricolas->sortBy(function ($lineaTechnician) {
            return $lineaTechnician->technician->level;
        })->values();

        $lineaConstruccion = Linea::where('nombre', 'Construccion')->first();
        $calificacionesConstruccion = $lineaConstruccion->lineaTechnician()
            ->where('linea_id', $lineaConstruccion->id)
            ->with(['technician', 'qualification'])
            ->get();
        $calificacionesConstruccion = $calificacionesConstruccion->sortBy(function ($lineaTechnician) {
            return $lineaTechnician->technician->level;
        })->values();

        return $this->respond([
            'agricola' => $calificacionesAgricolas,
            'construccion' => $calificacionesConstruccion
        ]);
    }

    public function changeTypeTechnician(Empleado $empleado, Technician $technician)
    {
        $empleado->update([
            'technician_id' => $technician->id,
        ]);

        return $this->respond(['message' => 'Técnico actualizado con éxito']);
    }

    public function getTechnicianLine(Linea $linea)
    {
        $lineaTechnicians = $linea->lineaTechnician()->with('technician')->get();
        $lineaTechnicians = $lineaTechnicians->sortBy(function ($lineaTechnician) {
            return $lineaTechnician->technician->level;
        })->values();

        $techniciansArray = $lineaTechnicians->map(function ($lineaTechnician) {
            return $lineaTechnician->technician;
        });

        return $this->respond($techniciansArray);
    }

    public function setUserX(Empleado $empleado, Request $request)
    {
        $request->validate(['usuario_x' => ['required', Rule::unique('empleados')->ignore($empleado->id)]]);

        $empleado->update([
            'usuario_x' => $request->usuario_x
        ]);

        return $this->respond(['message' => 'Usuario X actualizado con éxito']);
    }

    public function setProductivity(Empleado $empleado, Request $request)
    {
        $request->validate(['productividad' => ['required']]);

        $empleado->update([
            'productividad' => $request->productividad
        ]);

        return $this->respond(['message' => 'Productividad actualizada con éxito']);
    }

    public function getConstruccionBySucursal(Sucursal $sucursal)
    {
        // Obtener empleados de la sucursal que tengan el puesto de técnico y sean de línea de construcción
        $tecnicos = Empleado::where('sucursal_id', $sucursal->id)
            ->whereHas('puesto', function ($query) {
                $query->where('nombre', 'tecnico');
            })
            ->whereHas('linea', function ($query) {
                $query->where('nombre', 'construccion');
            })
            ->with('sucursal', 'technician') // Cargar la relación 'sucursal'
            ->get();

        $post = Post::whereHas('estatus', function ($query) {
            $query->where('nombre', 'Pantalla');
        })
            ->whereHas('linea', function ($query) {
                $query->where('nombre', 'construccion');
            })
            ->with('postDoc') // Cargar la relación 'sucursal'
            ->get();

        $data = [
            'tecnicos' => $tecnicos,
            'post' => $post
        ];

        return $this->respond($data);
    }

}
