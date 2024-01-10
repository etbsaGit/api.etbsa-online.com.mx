<?php

namespace App\Http\Controllers\Api;

use App\Models\Estatus;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Estatus\PutRequest;
use App\Http\Requests\Estatus\StoreRequest;

class EstatusController extends ApiController
{
    public function index()
    {
        return response()->json(Estatus::paginate(5));
    }

    public function all()
    {
        return response()->json(Estatus::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Estatus::create($request->validated()));
    }

    public function show(Estatus $estatus)
    {
        return response()->json($estatus);
    }

    public function update(PutRequest $request, Estatus $estatus)
    {
        $estatus->update($request->validated());
        return response()->json($estatus);
    }

    public function destroy(Estatus $estatus)
    {
        $estatus->delete();
        return response()->json("ok");
    }
}
