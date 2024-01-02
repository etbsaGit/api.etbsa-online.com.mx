<?php

namespace App\Http\Controllers\Api;

use App\Models\Antiguedad;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Antiguedad\PutRequest;
use App\Http\Requests\Antiguedad\StoreRequest;

class AntiguedadController extends ApiController
{
    public function index()
    {
        return response()->json(Antiguedad::paginate(5));
    }

    public function all()
    {
        return response()->json(Antiguedad::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Antiguedad::create($request->validated()));
    }

    public function show(Antiguedad $antiguedad)
    {
        return response()->json($antiguedad);
    }

    public function update(PutRequest $request, Antiguedad $antiguedad)
    {
        $antiguedad->update($request->validated());
        return response()->json($antiguedad);
    }

    public function destroy(Antiguedad $antiguedad)
    {
        $antiguedad->delete();
        return response()->json("ok");
    }
}