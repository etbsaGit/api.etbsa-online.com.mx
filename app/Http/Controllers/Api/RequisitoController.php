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

    public function all()
    {
        $requisitos = Requisito::get();
        return $this->respond($requisitos);
    }

    public function store(StoreRequest $request)
    {
        return $this->respond(Requisito::create($request->validated()));
    }

    public function show(Requisito $requisito)
    {
        return $this->respond($requisito->load('expediente'));
    }

    public function update(PutRequest $request, Requisito $requisito)
    {
        $requisito->update($request->validated());
        return $this->respond($requisito);
    }

    public function destroy(Requisito $requisito)
    {
        $requisito->delete();
        return $this->respond("ok");
    }
}
