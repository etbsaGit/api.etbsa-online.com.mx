<?php

namespace App\Http\Controllers\Caja;

use Illuminate\Http\Request;
use App\Models\Caja\CajaBanco;
use App\Models\Caja\CajaCuenta;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Caja\CajaCuenta\PutRequest;
use App\Http\Requests\Caja\CajaCuenta\StoreRequest;

class CajaCuentaController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(CajaCuenta::filter($filters)->with('cajaBanco')->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $cajaCuentum = CajaCuenta::create($request->validated());

        return $this->respondCreated($cajaCuentum);
    }

    /**
     * Display the specified resource.
     */
    public function show(CajaCuenta $cajaCuentum)
    {
        return $this->respond($cajaCuentum);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, CajaCuenta $cajaCuentum)
    {
        $cajaCuentum->update($request->validated());
        return $this->respond($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CajaCuenta $cajaCuentum)
    {
        $cajaCuentum->delete();
        return $this->respondSuccess();
    }

    public function getforms()
    {
        $data = [
            'bancos' => CajaBanco::all()
        ];
        return $this->respond($data);
    }
}
