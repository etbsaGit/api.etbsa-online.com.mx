<?php

namespace App\Http\Controllers\Caja;

use Illuminate\Http\Request;
use App\Models\Caja\CajaBanco;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Caja\CajaBanco\PutRequest;
use App\Http\Requests\Caja\CajaBanco\StoreRequest;

class CajaBancoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(CajaBanco::filter($filters)->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $cajaBanco = CajaBanco::create($request->validated());

        return $this->respondCreated($cajaBanco);
    }

    /**
     * Display the specified resource.
     */
    public function show(CajaBanco $cajaBanco)
    {
        return $this->respond($cajaBanco);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, CajaBanco $cajaBanco)
    {
        $cajaBanco->update($request->validated());
        return $this->respond($cajaBanco);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CajaBanco $cajaBanco)
    {
        $cajaBanco->delete();
        return $this->respondSuccess();
    }
}
