<?php

namespace App\Http\Controllers\Api;

use App\Models\Alergia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Alergia\PutRequest;
use App\Http\Requests\Alergia\StoreRequest;

class AlergiaController extends Controller
{
    public function index()
    {
        return response()->json(Alergia::paginate(5));
    }

    public function all()
    {
        return response()->json(Alergia::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Alergia::create($request->validated()));
    }

    public function show(Alergia $alergia)
    {
        return response()->json($alergia);
    }

    public function update(PutRequest $request, Alergia $alergia)
    {
        $alergia->update($request->validated());
        return response()->json($alergia);
    }

    public function destroy(Alergia $alergia)
    {
        $alergia->delete();
        return response()->json("ok");
    }
}
