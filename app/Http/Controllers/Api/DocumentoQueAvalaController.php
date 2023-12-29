<?php

namespace App\Http\Controllers\Api;

use App\Models\DocumentoQueAvala;
use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentoQueAvala\PutRequest;
use App\Http\Requests\DocumentoQueAvala\StoreRequest;

class DocumentoQueAvalaController extends Controller
{
    public function index()
    {
        return response()->json(DocumentoQueAvala::paginate(5));
    }

    public function all()
    {
        return response()->json(DocumentoQueAvala::get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(DocumentoQueAvala::create($request->validated()));
    }

    public function show(DocumentoQueAvala $documentoQueAvala)
    {
        return response()->json($documentoQueAvala);
    }

    public function update(PutRequest $request, DocumentoQueAvala $documentoQueAvala)
    {
        $documentoQueAvala->update($request->validated());
        return response()->json($documentoQueAvala);
    }

    public function destroy(DocumentoQueAvala $documentoQueAvala)
    {
        $documentoQueAvala->delete();
        return response()->json("ok");
    }
}