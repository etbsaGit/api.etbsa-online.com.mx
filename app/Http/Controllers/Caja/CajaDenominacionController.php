<?php

namespace App\Http\Controllers\Caja;

use Illuminate\Http\Request;
use App\Models\Caja\CajaDenominacion;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Caja\CajaDenominacion\PutRequest;
use App\Http\Requests\Caja\CajaDenominacion\StoreRequest;

class CajaDenominacionController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(CajaDenominacion::filter($filters)->paginate(15));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        // Verificar si ya existe una denominación con el mismo valor y tipo
        $existe = CajaDenominacion::where('valor', $request->valor)
            ->where('tipo', $request->tipo)
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'Ya existe una denominación con ese valor y tipo.'
            ], 422);
        }

        // Si no existe, creamos la denominación
        $denominacion = CajaDenominacion::create($request->validated());

        return $this->respondCreated($denominacion);
    }

    /**
     * Display the specified resource.
     */
    public function show(CajaDenominacion $cajaDenominacion)
    {
        return $this->respond($cajaDenominacion);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, CajaDenominacion $cajaDenominacion)
    {
        // Verificar si ya existe otra denominación con el mismo valor y tipo
        $existe = CajaDenominacion::where('valor', $request->valor)
            ->where('tipo', $request->tipo)
            ->where('id', '!=', $cajaDenominacion->id) // Excluir el registro que estamos editando
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'Ya existe otra denominación con ese valor y tipo.'
            ], 422);
        }

        // Si no existe conflicto, actualizamos
        $cajaDenominacion->update($request->validated());

        return $this->respond($cajaDenominacion);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CajaDenominacion $cajaDenominacion)
    {
        $cajaDenominacion->delete();
        return $this->respondSuccess();
    }
}
