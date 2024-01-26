<?php

namespace App\Http\Controllers\Api;

use App\Models\Plantilla;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Plantilla\PutRequest;
use App\Http\Requests\Plantilla\StoreRequest;

class PlantillaController extends ApiController
{
    public function index()
    {
        return response()->json(Plantilla::paginate(5));
    }

    public function all()
    {
        return response()->json(Plantilla::with(['requisito'])->get());
    }

    public function store(StoreRequest $request)
    {
        $plantilla = Plantilla::create(
            $request->only('nombre')
        );

        $plantilla->requisito()->syncWithoutDetaching(
            $request->get('requisito_id')
        );

        return response()->json($plantilla);
    }

    public function show(Plantilla $plantilla)
    {
        return response()->json($plantilla);
    }

    public function update(PutRequest $request, Plantilla $plantilla)
    {
        $plantilla->update($request->only('nombre'));

        $plantilla->requisito()->sync($request->get('requisito_id'));

        return response()->json($plantilla);
    }

    public function destroy(Plantilla $plantilla)
    {
        $plantilla->delete();
        return response()->json("ok");
    }
}
