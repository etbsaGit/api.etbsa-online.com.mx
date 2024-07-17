<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Marca;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Marca\StoreMarcaRequest;

class MarcaController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Marca::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMarcaRequest $request)
    {
        $marca = Marca::create($request->validated());
        return $this->respondCreated($marca);
    }

    /**
     * Display the specified resource.
     */
    public function show(Marca $marca)
    {
        return $this->respond($marca);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreMarcaRequest $request, Marca $marca)
    {
        $marca->update($request->validated());
        return $this->respond($marca);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Marca $marca)
    {
        $marca->delete();
        return $this->respondSuccess();
    }
}
