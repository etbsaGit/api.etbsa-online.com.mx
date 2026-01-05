<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Ganado;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Ganado\GanadoRequest;

class GanadoController extends ApiController
{
    /**
     * Listado
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            Ganado::filter($filters)->paginate(10),
            'Listado de ganado cargado correctamente'
        );
    }

    /**
     * Crear
     */
    public function store(GanadoRequest $request)
    {
        $ganado = Ganado::create($request->validated());

        return $this->respondCreated(
            $ganado,
            'Ganado registrado correctamente'
        );
    }

    /**
     * Mostrar
     */
    public function show(Ganado $ganado)
    {
        return $this->respond(
            $ganado,
            'Detalle del ganado'
        );
    }

    /**
     * Actualizar
     */
    public function update(GanadoRequest $request, Ganado $ganado)
    {
        $ganado->update($request->validated());

        return $this->respond(
            $ganado,
            'Ganado actualizado correctamente'
        );
    }

    /**
     * Eliminar
     */
    public function destroy(Ganado $ganado)
    {
        $ganado->delete();

        return $this->respondSuccess(
            'Ganado eliminado correctamente'
        );
    }
}
