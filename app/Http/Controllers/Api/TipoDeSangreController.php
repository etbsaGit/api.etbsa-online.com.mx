<?php

namespace App\Http\Controllers\Api;

use App\Models\TipoDeSangre;
use App\Http\Controllers\ApiController;
use App\Http\Requests\TipoDeSangre\PutRequest;
use App\Http\Requests\TipoDeSangre\StoreRequest;

class TipoDeSangreController extends ApiController
{
    public function index()
    {
        return response()->json(TipoDeSangre::paginate(5));
    }

    public function all()
    {
        return response()->json(TipoDeSangre::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(TipoDeSangre::create($request->validated()));
    }

    public function show(TipoDeSangre $tipoDeSangre)
    {
        return response()->json($tipoDeSangre);
    }

    public function update(PutRequest $request, TipoDeSangre $tipoDeSangre)
    {
        $tipoDeSangre->update($request->validated());
        return response()->json($tipoDeSangre);
    }

    public function destroy(TipoDeSangre $tipoDeSangre)
    {
        $tipoDeSangre->delete();
        return response()->json("ok");
    }
}