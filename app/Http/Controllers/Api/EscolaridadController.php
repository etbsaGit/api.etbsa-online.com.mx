<?php

namespace App\Http\Controllers\Api;

use App\Models\Escolaridad;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Escolaridad\PutRequest;
use App\Http\Requests\Escolaridad\StoreRequest;

class EscolaridadController extends ApiController
{
    public function index()
    {
        return $this->respond(Escolaridad::paginate(5));
    }

    public function all()
    {
        return $this->respond(Escolaridad::get());
    }

    public function store(StoreRequest $request)
    {
        return $this->respond(Escolaridad::create($request->validated()));
    }

    public function show(Escolaridad $escolaridad)
    {
        return $this->respond($escolaridad);
    }

    public function update(PutRequest $request, Escolaridad $escolaridad)
    {
        $escolaridad->update($request->validated());
        return $this->respond($escolaridad);
    }

    public function destroy(Escolaridad $escolaridad)
    {
        $escolaridad->delete();
        return $this->respond("ok");
    }
}