<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\Classification;
use App\Http\Requests\Intranet\Classifications\ClassificationRequest;

class ClassificationsController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            Classification::filter($filters)->paginate(10),
            'Listado de clasificaciones cargado correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClassificationRequest $request)
    {
        $classification = Classification::create($request->validated());

        return $this->respondCreated(
            $classification,
            'Clasificacion registrada correctamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Classification $classification)
    {
        return $this->respond(
            $classification,
            'Detalle de la clasificacion'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClassificationRequest $request, Classification $classification)
    {
        $classification->update($request->validated());

        return $this->respond(
            $classification,
            'Clasificacion actualizada correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Classification $classification)
    {
        $classification->delete();

        return $this->respondSuccess(
            'Clasificacion eliminada correctamente'
        );
    }
}
