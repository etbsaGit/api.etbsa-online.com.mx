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
        return $this->respond(Archivo::paginate(5));
    }

    public function all()
    {
        return $this->respond(Archivo::with('asignable')->get());
    }

    public function store(StoreRequest $request)
    {
        return $this->respond(Archivo::create($request->validated()));
    }

    public function show(Archivo $archivo)
    {
        return $this->respond($archivo->load('asignable'));
    }

    public function update(PutRequest $request, Archivo $archivo)
    {
        $archivo->update($request->validated());
        return $this->respond($archivo);
    }

    public function destroy($archivoId)
    {
        $archivo = Archivo::find($archivoId);

        if (!$archivo) {
            return $this->respond(['error' => 'Archivo no encontrado.'], 404);
        }

        $documento = $archivo->asignable();

        if (!$documento) {
            return $this->respond(['error' => 'Documento no encontrado.'], 404);
        }

        if (Storage::disk('s3')->exists($archivo->path)) {
            Storage::disk('s3')->delete($archivo->path);
        }

        $estatus = Estatus::where('clave', 'pendiente')->first();

        $documento->update(['estatus_id' => $estatus->id]);

        $archivo->delete();

        return $this->respond('Archivo borrado exitosamente');
    }
}
