<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\ConstructionClassification;
use App\Http\Requests\Intranet\ClassConst\ConstructionClassificationRequest;

class ConstructionClassificationsController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            ConstructionClassification::filter($filters)->paginate(10),
            'Listado cargado correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ConstructionClassificationRequest $request)
    {
        $constructionClassification = ConstructionClassification::create($request->validated());

        return $this->respondCreated(
            $constructionClassification,
            'Registrado correctamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(ConstructionClassification $constructionClassification)
    {
        return $this->respond(
            $constructionClassification,
            'Detalle del registro'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ConstructionClassificationRequest $request, ConstructionClassification $constructionClassification)
    {
        $constructionClassification->update($request->validated());

        return $this->respond(
            $constructionClassification,
            'Actualizado correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConstructionClassification $constructionClassification)
    {
        $constructionClassification->delete();

        return $this->respondSuccess(
            'Eliminado correctamente'
        );
    }
}
