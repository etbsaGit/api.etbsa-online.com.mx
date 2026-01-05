<?php

namespace App\Http\Controllers\Api;

use App\Models\Estatus;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Estatus\PutRequest;
use App\Http\Requests\Estatus\StoreRequest;
use Illuminate\Http\Request;


class EstatusController extends ApiController
{
    public function index(Request $request)
    {
        $filters = $request->all();

        $estatuses = Estatus::filter($filters)
            ->orderBy('tipo_estatus')
            ->paginate(10);
        return $this->respond($estatuses);
    }

    public function store(StoreRequest $request)
    {
        return $this->respond(Estatus::create($request->validated()));
    }

    public function show(Estatus $estatus)
    {
        return $this->respond($estatus);
    }

    public function update(PutRequest $request, Estatus $estatus)
    {
        $estatus->update($request->validated());
        return $this->respond($estatus);
    }

    public function destroy(Estatus $estatus)
    {
        $estatus->delete();
        return $this->respond("ok");
    }

    public function getPerType($type)
    {
        $statuses = Estatus::where('tipo_estatus', $type)->orderBy('nombre', 'asc')->get();

        return $this->respond($statuses);
    }
}
