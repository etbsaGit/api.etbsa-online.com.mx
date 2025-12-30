<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Tactic;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Tactic\TacticRequest;

class TacticController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            Tactic::filter($filters)->paginate(10),
            'Listado de tacticas cargado correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TacticRequest $request)
    {
        $tactic = Tactic::create($request->validated());
        return $this->respondCreated(
            $tactic,
            'Tactica registrada correctamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Tactic $tactic)
    {
        return $this->respond(
            $tactic,
            'Detalle de la tactica'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TacticRequest $request, Tactic $tactic)
    {
        $tactic->update($request->validated());

        return $this->respond(
            $tactic,
            'Tactica actualizada correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tactic $tactic)
    {
        $tactic->delete();

        return $this->respondSuccess(
            'Tactica eliminada correctamente'
        );
    }
}
