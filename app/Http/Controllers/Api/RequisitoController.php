<?php

namespace App\Http\Controllers\Api;

use App\Models\Requisito;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Requisito\PutRequest;
use App\Http\Requests\Requisito\StoreRequest;

class RequisitoController extends ApiController
{
    public function index(Request $request)
    {
        $filters = $request->all();
        $requisitos = Requisito::filter($filters)->paginate(10);
        return $this->respond($requisitos);
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Requisito::create($request->validated()));
    }

    public function show(Requisito $requisito)
    {
        return response()->json($requisito->load('expediente'));
    }

    public function update(PutRequest $request, Requisito $requisito)
    {
        $requisito->update($request->validated());
        return response()->json($requisito);
    }

    public function destroy(Requisito $requisito)
    {
        $requisito->delete();
        return response()->json("ok");
    }
}
