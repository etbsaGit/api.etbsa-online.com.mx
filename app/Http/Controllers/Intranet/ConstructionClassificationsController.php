<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\ClassConst\CreateClassConstRequest;
use App\Models\Intranet\ConstructionClassification;

class ConstructionClassificationsController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(ConstructionClassification::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateClassConstRequest $request)
    {
        $constructionClassification = ConstructionClassification::create($request->validated());
        return $this->respondCreated($constructionClassification);
    }

    /**
     * Display the specified resource.
     */
    public function show(ConstructionClassification $constructionClassification)
    {
        return $this->respond($constructionClassification);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CreateClassConstRequest $request, ConstructionClassification $constructionClassification)
    {
        $constructionClassification->update($request->validated());
        return $this->respond($constructionClassification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConstructionClassification $constructionClassification)
    {
        $constructionClassification->delete();
        return $this->respondSuccess();
    }
}
