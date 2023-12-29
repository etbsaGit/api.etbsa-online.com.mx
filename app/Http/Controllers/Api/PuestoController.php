<?php

namespace App\Http\Controllers\Api;

use App\Models\Puesto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Puesto\PutRequest;
use App\Http\Requests\Puesto\StoreRequest;

class PuestoController extends Controller
{
    public function index()
    {
        return response()->json(Puesto::paginate(5));
    }

    public function all()
    {
        return response()->json(Puesto::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Puesto::create($request->validated()));
    }

    public function show(Puesto $puesto)
    {
        return response()->json($puesto);
    }

    public function update(PutRequest $request, Puesto $puesto)
    {
        $puesto->update($request->validated());
        return response()->json($puesto);
    }

    public function destroy(Puesto $puesto)
    {
        $puesto->delete();
        return response()->json("ok");
    }
}