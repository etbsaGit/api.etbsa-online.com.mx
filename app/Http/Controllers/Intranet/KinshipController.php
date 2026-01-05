<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Kinship;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Kinship\KinshipRequest;

class KinshipController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            Kinship::filter($filters)->paginate(10),
            'Listado de parentescos cargado correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(KinshipRequest $request)
    {
        $kinship = Kinship::create($request->validated());

        return $this->respondCreated(
            $kinship,
            'Parentesco registrado correctamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Kinship $kinship)
    {
        return $this->respond(
            $kinship,
            'Detalle del parentesco'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(KinshipRequest $request, Kinship $kinship)
    {
        $kinship->update($request->validated());

        return $this->respond(
            $kinship,
            'Parentesco actualizado correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kinship $kinship)
    {
        $kinship->delete();
        return $this->respondSuccess(
            'Parentesco eliminado correctamente'
        );
    }
}
