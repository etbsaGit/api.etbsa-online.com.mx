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
        return $this->respond(TipoDeSangre::paginate(5));
    }

    public function all()
    {
        return $this->respond(TipoDeSangre::get());
    }

    public function store(StoreRequest $request)
    {
        return $this->respond(TipoDeSangre::create($request->validated()));
    }

    public function show(TipoDeSangre $tipoDeSangre)
    {
        return $this->respond($tipoDeSangre);
    }

    public function update(PutRequest $request, TipoDeSangre $tipoDeSangre)
    {
        $tipoDeSangre->update($request->validated());
        return $this->respond($tipoDeSangre);
    }

    public function destroy(TipoDeSangre $tipoDeSangre)
    {
        $tipoDeSangre->delete();
        return $this->respond("ok");
    }
}