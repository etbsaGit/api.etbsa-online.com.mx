<?php

namespace App\Http\Controllers\Api;

use App\Models\Sucursal;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Sucursal\PutRequest;
use App\Http\Requests\Sucursal\StoreRequest;

class SucursalController extends ApiController
{
    public function index()
    {
        return response()->json(Sucursal::paginate(5));
    }

    public function all()
    {
        return response()->json(Sucursal::with(['linea'])->get());
    }

    public function store(StoreRequest $request)
    {
        $sucursal = Sucursal::create($request->only(['nombre', 'direccion']));

        $sucursal->linea()->syncWithoutDetaching(
            $request->get('linea_id')
        );

        return response()->json($sucursal);
    }

    public function show(Sucursal $sucursal)
    {
        return response()->json($sucursal);
    }

    public function update(PutRequest $request, Sucursal $sucursal)
    {
        $sucursal->update($request->only(['nombre', 'direccion']));
        $sucursal->linea()->sync($request->get('linea_id'));
        return response()->json($sucursal);
    }

    public function destroy(Sucursal $sucursal)
    {
        $sucursal->delete();
        return response()->json("ok");
    }
}
