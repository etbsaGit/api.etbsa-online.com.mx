<?php

namespace App\Http\Controllers\Api;

use App\Models\Archivo;
use App\Models\Documento;
use App\Traits\UploadableFile;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Documento\PutRequest;
use App\Http\Requests\Documento\StoreRequest;
use App\Http\Requests\Archivo\StoreRequest as ArchivoStoreRequest;
use App\Models\Estatus;

class DocumentoController extends ApiController
{

    use UploadableFile;
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

    public function uploadFile(ArchivoStoreRequest $request, Documento $documento)
    {
        if ($request->hasFile('file')) {
            $archivo = $request->file('file');


            $path = $this->uploadOne($archivo, $documento->default_path_folder, 's3');

            if (!$path) {
                return response()->json(['error' => 'Error al Guardar el Archivo.'], 404);
            }


            $archivoBD = new Archivo([
                'nombre' => $archivo->getClientOriginalName(),
                'tipo_de_archivo' => $archivo->getClientOriginalExtension(),
                'tamano_de_archivo' => $archivo->getSize() / 1024,
                'path' => $path,
            ]);
            $documento->asignable()->save($archivoBD);

            $estatus = Estatus::where('clave', 'pendiente')->first();
            $documento->estatus()->associate($estatus)->save();

            return response()->json($archivoBD);
        } else {
            return response()->json(['error' => 'No se ha enviado un archivo en la solicitud.'], 400);
        }
    }
}
