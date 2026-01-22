<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\InvFactory;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\InvFactory\InvFactoryRequest;

class InvFactoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            InvFactory::filter($filters)->paginate(10),
            'Listado de proveedores cargada correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InvFactoryRequest $request)
    {
        $invFactory = InvFactory::create($request->validated());

        return $this->respondCreated(
            $invFactory,
            'Proveedor registrado correctamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(InvFactory $invFactory)
    {
        return $this->respond(
            $invFactory,
            'Detalle del proveedor'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InvFactoryRequest $request, InvFactory $invFactory)
    {
        $invFactory->update($request->validated());

        return $this->respond(
            $invFactory,
            'Proveedor actualizado correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvFactory $invFactory)
    {
        $invFactory->delete();

        return $this->respondSuccess(
            'Proveedor eliminado correctamente'
        );
    }
}
