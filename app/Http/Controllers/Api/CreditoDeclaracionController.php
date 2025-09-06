<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\CreditoConcepto;
use App\Models\CreditoRelacion;
use App\Models\Intranet\Cliente;
use App\Models\CreditoDeclaracion;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\CreditoDeclaracion\StoreRequest;

class CreditoDeclaracionController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(CreditoDeclaracion::filter($filters)->with('cliente', 'relaciones')->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        // Extraemos solo los datos que pertenecen a credito_declaraciones
        $data = $request->except('relaciones');

        // Creamos la declaración
        $creditoDeclaracion = CreditoDeclaracion::create($data);

        // Creamos las relaciones
        if ($request->has('relaciones')) {
            foreach ($request->input('relaciones') as $relacion) {
                // Asignamos el id de la declaración recién creada
                $relacion['credito_declaracion_id'] = $creditoDeclaracion->id;
                CreditoRelacion::create($relacion);
            }
        }

        return $this->respondCreated($creditoDeclaracion->load('relaciones'));
    }


    /**
     * Display the specified resource.
     */
    public function show(CreditoDeclaracion $creditoDeclaracion)
    {
        return $this->respond($creditoDeclaracion->load('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, CreditoDeclaracion $creditoDeclaracion)
    {
        // Actualizamos la declaración con los datos principales
        $data = $request->except('relaciones');
        $creditoDeclaracion->update($data);

        // Manejo de relaciones
        if ($request->has('relaciones')) {
            foreach ($request->input('relaciones') as $relacionData) {
                if (!empty($relacionData['id'])) {
                    // Si tiene ID, la buscamos y actualizamos
                    $relacion = CreditoRelacion::find($relacionData['id']);
                    if ($relacion) {
                        $relacion->update($relacionData);
                    }
                } else {
                    // Si no tiene ID, la creamos vinculada a esta declaración
                    $relacionData['credito_declaracion_id'] = $creditoDeclaracion->id;
                    CreditoRelacion::create($relacionData);
                }
            }
        }
        return $this->respond($creditoDeclaracion->load('relaciones'));
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CreditoDeclaracion $creditoDeclaracion)
    {
        // Borrar todas las relaciones asociadas
        $creditoDeclaracion->relaciones()->delete();

        // Borrar la declaración
        $creditoDeclaracion->delete();

        return $this->respondSuccess();
    }


    public function getforms(Request $request)
    {
        $filters = $request->all();

        $data = [
            'clientes'  => Cliente::filter($filters)->orderBy('nombre', 'asc')->get(),
            'conceptos' => CreditoConcepto::all(),
        ];

        return $this->respond($data);
    }

    public function changeEstatus(CreditoDeclaracion $creditoDeclaracion, int $status)
    {
        $creditoDeclaracion->status = $status;
        $creditoDeclaracion->save();

        return $this->respondSuccess();
    }
}
