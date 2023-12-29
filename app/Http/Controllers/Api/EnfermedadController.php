<?php

namespace App\Http\Controllers\Api;

use App\Models\Enfermedad;
use App\Http\Controllers\Controller;
use App\Http\Requests\Enfermedad\PutRequest;
use App\Http\Requests\Enfermedad\StoreRequest;

class EnfermedadController extends Controller
{
    public function index()
    {
        return response()->json(Enfermedad::paginate(5));
    }

    public function all()
    {
        return response()->json(Enfermedad::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Enfermedad::create($request->validated()));
    }

    public function show(Enfermedad $enfermedad)
    {
        return response()->json($enfermedad);
    }

    public function update(PutRequest $request, Enfermedad $enfermedad)
    {
        $enfermedad->update($request->validated());
        return response()->json($enfermedad);
    }

    public function destroy(Enfermedad $enfermedad)
    {
        $enfermedad->delete();
        return response()->json("ok");
    }
}