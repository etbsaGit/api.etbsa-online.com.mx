<?php

namespace App\Http\Controllers\Api;

use App\Models\Archivo;
use App\Models\Documento;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Archivo\PutRequest;
use App\Http\Requests\Archivo\StoreRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public function destroy(Archivo $archivo)
    {
        $archivo->delete();
        return response()->json("ok");
    }

    public function uploadFile(StoreRequest $request)
    {
        if ($request->hasFile('file')) {
            $archivo = $request->file('file');

            $nombre = $archivo->getClientOriginalName();
            $extension = $archivo->getClientOriginalExtension();
            $tamaño = $archivo->getSize() / 1024; // Tamaño en KB
            $path = $archivo->store('pdf', 'public');
            $asignableId = $request->input('asignableId');

            $asignable = Documento::find($asignableId);

            if (!$asignable) {
                return response()->json(['error' => 'Asignable no encontrado.'], 404);
            }

            $archivoBD = new Archivo([
                'nombre' => $nombre,
                'tipo_de_archivo' => $extension,
                'tamano_de_archivo' => $tamaño,
                'path' => $path,
            ]);

            $asignable->asignable()->save($archivoBD);

            $archivoBD->save();

            return response()->json($archivoBD);
        } else {
            return response()->json(['error' => 'No se ha enviado un archivo en la solicitud.'], 400);
        }
    }

    public function deleteFile($archivoId)
    {
        $archivo = Archivo::find($archivoId);

        $archivoPath = public_path() . $archivo->path;

        if (!$archivo) {
            return response()->json(['error' => 'Archivo no encontrado.'], 404);
        }

        if (file_exists($archivoPath)) {
            unlink($archivoPath);
            $archivo->delete();
            return response()->json('El archivo ha sido borrado');
        } else {
            return response()->json('El archivo no existe');
        }
    }



    public function showFile($archivoId)
    {
        $archivoDB = Archivo::find($archivoId);

        if (!$archivoDB) {
            return response()->json(['error' => 'Archivo no encontrado.'], 404);
        }

        return response($archivoDB->path);
    }
}
