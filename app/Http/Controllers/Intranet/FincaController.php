<?php

namespace App\Http\Controllers\Intranet;

use App\Models\Estatus;
use Illuminate\Http\Request;
use App\Models\Intranet\Finca;
use App\Models\Intranet\Cliente;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Finca\StoreRequest;

class FincaController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Finca::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $finca = Finca::create($request->validated());
        return $this->respondCreated($finca);
    }

    /**
     * Display the specified resource.
     */
    public function show(Finca $finca)
    {
        return $this->respond($finca);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, Finca $finca)
    {
        $finca->update($request->validated());
        return $this->respond($finca);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Finca $finca)
    {
        $finca->delete();
        return $this->respondSuccess();
    }

    public function getPerCliente(Cliente $cliente)
    {
        $fincas = Finca::where('cliente_id', $cliente->id)
            ->with('estatus')
            ->get();

        return $this->respond($fincas);
    }

    public function getOptions()
    {
        $data = [
            'estatus' => Estatus::where('tipo_estatus', 'finca')->get()
        ];

        return $this->respond($data);
    }
}
