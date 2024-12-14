<?php

namespace App\Http\Controllers\Api;

use App\Models\Linea;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Linea\PutRequest;
use App\Http\Requests\Linea\StoreRequest;

class LineaController extends ApiController
{
    public function index(Request $request)
    {
        $filters = $request->all();
        $lineas = Linea::filter($filters)->paginate(10);
        return $this->respond($lineas);
    }

    public function all()
    {
        return response()->json(Linea::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Linea::create($request->validated()));
    }

    public function show(Linea $linea)
    {
        return response()->json($linea);
    }

    public function update(PutRequest $request, Linea $linea)
    {
        $linea->update($request->validated());
        return response()->json($linea);
    }

    public function destroy(Linea $linea)
    {
        $linea->delete();
        return response()->json("ok");
    }
}
