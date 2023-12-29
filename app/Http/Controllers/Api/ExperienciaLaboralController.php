<?php

namespace App\Http\Controllers\Api;

use App\Models\ExperienciaLaboral;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExperienciaLaboral\PutRequest;
use App\Http\Requests\ExperienciaLaboral\StoreRequest;

class ExperienciaLaboralController extends Controller
{
    public function index()
    {
        return response()->json(ExperienciaLaboral::paginate(5));
    }

    public function all()
    {
        return response()->json(ExperienciaLaboral::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(ExperienciaLaboral::create($request->validated()));
    }

    public function show(ExperienciaLaboral $experienciaLaboral)
    {
        return response()->json($experienciaLaboral);
    }

    public function update(PutRequest $request, ExperienciaLaboral $experienciaLaboral)
    {
        $experienciaLaboral->update($request->validated());
        return response()->json($experienciaLaboral);
    }

    public function destroy(ExperienciaLaboral $experienciaLaboral)
    {
        $experienciaLaboral->delete();
        return response()->json("ok");
    }
}