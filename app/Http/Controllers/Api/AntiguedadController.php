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
        return $this->respond(Antiguedad::paginate(5));
    }

    public function all()
    {
        return $this->respond(Antiguedad::get());
    }

    public function store(StoreRequest $request)
    {
        return $this->respond(Antiguedad::create($request->validated()));
    }

    public function show(Antiguedad $antiguedad)
    {
        return $this->respond($antiguedad);
    }

    public function update(PutRequest $request, Antiguedad $antiguedad)
    {
        $antiguedad->update($request->validated());
        return $this->respond($antiguedad);
    }

    public function destroy(Antiguedad $antiguedad)
    {
        $antiguedad->delete();
        return $this->respond("ok");
    }
}