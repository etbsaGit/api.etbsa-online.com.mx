<?php

namespace App\Http\Controllers\Api;

use App\Models\Sucursal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sucursal\PutRequest;
use App\Http\Requests\Sucursal\StoreRequest;

class SucursalController extends Controller
{
    public function index()
    {
        return response()->json(Sucursal::paginate(5));
    }

    public function all()
    {
        return response()->json(Sucursal::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Sucursal::create($request->validated()));
    }

    public function show(Sucursal $sucursal)
    {
        return response()->json($sucursal);
    }

    public function update(PutRequest $request, Sucursal $sucursal)
    {
        $sucursal->update($request->validated());
        return response()->json($sucursal);
    }

    public function destroy(Sucursal $sucursal)
    {
        $sucursal->delete();
        return response()->json("ok");
    }
}