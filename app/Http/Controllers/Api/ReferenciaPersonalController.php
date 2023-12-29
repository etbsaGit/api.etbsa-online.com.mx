<?php

namespace App\Http\Controllers\Api;

use App\Models\ReferenciaPersonal;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReferenciaPersonal\PutRequest;
use App\Http\Requests\ReferenciaPersonal\StoreRequest;

class ReferenciaPersonalController extends Controller
{
    public function index()
    {
        return response()->json(ReferenciaPersonal::paginate(5));
    }

    public function all()
    {
        return response()->json(ReferenciaPersonal::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(ReferenciaPersonal::create($request->validated()));
    }

    public function show(ReferenciaPersonal $referenciaPersonal)
    {
        return response()->json($referenciaPersonal);
    }

    public function update(PutRequest $request, ReferenciaPersonal $referenciaPersonal)
    {
        $referenciaPersonal->update($request->validated());
        return response()->json($referenciaPersonal);
    }

    public function destroy(ReferenciaPersonal $referenciaPersonal)
    {
        $referenciaPersonal->delete();
        return response()->json("ok");
    }
}