<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\CreditoRelacion;
use App\Http\Controllers\ApiController;

class CreditoRelacionController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CreditoRelacion $creditoRelacion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CreditoRelacion $creditoRelacion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CreditoRelacion $creditoRelacion)
    {
        $creditoRelacion->delete();

        return $this->respondSuccess();
    }
}
