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
        return response()->json(Estatus::create($request->validated()));
    }

    public function show(Estatus $estatus)
    {
        return response()->json($estatus);
    }

    public function update(PutRequest $request, Estatus $estatus)
    {
        $estatus->update($request->validated());
        return response()->json($estatus);
    }

    public function destroy(Estatus $estatus)
    {
        $estatus->delete();
        return response()->json("ok");
    }

    public function getPerType($type)
    {
        $statuses = Estatus::where('tipo_estatus', $type)->orderBy('nombre', 'asc')->get();

        return response()->json($statuses);
    }
}
