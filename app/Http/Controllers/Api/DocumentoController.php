<?php

namespace App\Http\Controllers\Api;

use App\Models\Archivo;
use App\Models\Documento;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Documento\PutRequest;
use App\Http\Requests\Documento\StoreRequest;
use App\Http\Requests\Archivo\StoreRequest as ArchivoStoreRequest;
use App\Models\Estatus;

class DocumentoController extends ApiController
{
    public function index()
    {
        return response()->json(Documento::paginate(5));
    }

    public function all()
    {
        return response()->json(Documento::with('asignable')->get());
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Documento::create($request->validated()));
    }

    public function show(Documento $documento)
    {
        return response()->json($documento->load('asignable'));
    }

    public function update(PutRequest $request, Documento $documento)
    {
        $documento->update($request->validated());
        return response()->json($documento);
    }

    public function destroy(Documento $documento)
    {
        $documento->delete();
        return response()->json("ok");
    }

    public function uploadFile(ArchivoStoreRequest $request, Documento $documentoID)
    {
        if ($request->hasFile('file')) {
            $archivo = $request->file('file');

            $nombre = $archivo->getClientOriginalName();
            $extension = $archivo->getClientOriginalExtension();
            $tamaño = $archivo->getSize() / 1024; // Tamaño en KB
            $path = $archivo->store('pdf', 'public');

            if (!$documentoID) {
                return response()->json(['error' => 'Asignable no encontrado.'], 404);
            }

            $archivoBD = new Archivo([
                'nombre' => $nombre,
                'tipo_de_archivo' => $extension,
                'tamano_de_archivo' => $tamaño,
                'path' => $path,
            ]);

            $estatus = Estatus::where('clave', 'enviado')->first();

            $documentoID->estatus_id = $estatus->id;

            $documentoID->asignable()->save($archivoBD);

            $documentoID->save();

            $archivoBD->save();

            return response()->json($archivoBD);
        } else {
            return response()->json(['error' => 'No se ha enviado un archivo en la solicitud.'], 400);
        }
    }
}
