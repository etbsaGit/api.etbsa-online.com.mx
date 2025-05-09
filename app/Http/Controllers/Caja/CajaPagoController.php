<?php

namespace App\Http\Controllers\Caja;

use Illuminate\Http\Request;
use App\Models\Caja\CajaPago;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;

class CajaPagoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
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
    public function show(CajaPago $cajaPago)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CajaPago $cajaPago)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CajaPago $cajaPago)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CajaPago $cajaPago)
    {
        $cajaPago->delete();
        return $this->respondSuccess();
    }
}
