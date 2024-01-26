<?php

namespace App\Http\Controllers\Api;

use App\Models\Archivo;
use App\Models\Estatus;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Archivo\PutRequest;
use App\Http\Requests\Archivo\StoreRequest;
use Illuminate\Support\Facades\Storage;

class ArchivoController extends ApiController
{
    public function index()
    {
        return response()->json(Archivo::paginate(5));
    }

    public function all()
    {
        return response()->json(Archivo::with('asignable')->get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Archivo::create($request->validated()));
    }

    public function show(Archivo $archivo)
    {
        return response()->json($archivo->load('asignable'));
    }

    public function update(PutRequest $request, Archivo $archivo)
    {
        $archivo->update($request->validated());
        return response()->json($archivo);
    }

    public function destroy($archivoId)
    {
        $archivo = Archivo::find($archivoId);

        if (!$archivo) {
            return response()->json(['error' => 'Archivo no encontrado.'], 404);
        }

        $documento = $archivo->asignable();

        if (!$documento) {
            return response()->json(['error' => 'Documento no encontrado.'], 404);
        }

        if (Storage::disk('s3')->exists($archivo->path)) {
            Storage::disk('s3')->delete($archivo->path);
        }

        $estatus = Estatus::where('clave', 'pendiente')->first();

        $documento->update(['estatus_id' => $estatus->id]);

        $archivo->delete();

        return response()->json('Archivo borrado exitosamente');
    }
}
