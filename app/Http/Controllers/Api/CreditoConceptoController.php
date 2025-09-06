<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\CreditoConcepto;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\CreditoConcepto\StoreRequest;

class CreditoConceptoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(CreditoConcepto::filter($filters)->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $creditoConcepto = CreditoConcepto::create($request->validated());

        return $this->respondCreated($creditoConcepto);
    }

    /**
     * Display the specified resource.
     */
    public function show(CreditoConcepto $creditoConcepto)
    {
        return $this->respond($creditoConcepto);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, CreditoConcepto $creditoConcepto)
    {
        $creditoConcepto->update($request->validated());

        return $this->respond($creditoConcepto);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CreditoConcepto $creditoConcepto)
    {
        $creditoConcepto->delete();

        return $this->respondSuccess();
    }
}
