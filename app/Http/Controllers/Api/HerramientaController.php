<?php

namespace App\Http\Controllers\Api;

use App\Models\Herramienta;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Herramienta\PutRequest;
use App\Http\Requests\Herramienta\StoreRequest;

class HerramientaController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        return $this->respond(Herramienta::filter($filters)->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $herramientum = Herramienta::create($request->validated());
        return $this->respondCreated($herramientum);
    }

    /**
     * Display the specified resource.
     */
    public function show(Herramienta $herramientum)
    {
        return $this->respond($herramientum);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, Herramienta $herramientum)
    {
        $herramientum->update($request->validated());
        return $this->respond($herramientum);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Herramienta $herramientum)
    {
        $herramientum->delete();
        return $this->respondSuccess();
    }
}
