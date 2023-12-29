<?php

namespace App\Http\Controllers\Api;

use App\Models\Estudio;
use App\Http\Controllers\Controller;
use App\Http\Requests\Estudio\PutRequest;
use App\Http\Requests\Estudio\StoreRequest;

class EstudioController extends Controller
{
    public function index()
    {
        return response()->json(Estudio::paginate(5));
    }

    public function all()
    {
        return response()->json(Estudio::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Estudio::create($request->validated()));
    }

    public function show(Estudio $estudio)
    {
        return response()->json($estudio);
    }

    public function update(PutRequest $request, Estudio $estudio)
    {
        $estudio->update($request->validated());
        return response()->json($estudio);
    }

    public function destroy(Estudio $estudio)
    {
        $estudio->delete();
        return response()->json("ok");
    }
}