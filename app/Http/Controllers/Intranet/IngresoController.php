<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Traits\UploadableFile;
use App\Models\Intranet\Cliente;
use App\Models\Intranet\Ingreso;
use App\Models\Intranet\IngresoDoc;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Ingreso\StoreRequest;

class IngresoController extends ApiController
{
    use UploadableFile;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Ingreso::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $ingreso = Ingreso::create($request->validated());
        $docs = $request->base64;

        if (!empty($docs)) {
            foreach ($docs as $doc) {
                $sa = IngresoDoc::create([
                    "name" => $doc['name'],
                    "ingreso_id" => $ingreso->id
                ]);
                $relativePath  = $this->saveDoc($doc['base64'], $sa->default_path_folder);
                $sa->update(['path' => $relativePath]);
            }
        }

        return $this->respondCreated($ingreso);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ingreso $ingreso)
    {
        return $this->respond($ingreso);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, Ingreso $ingreso)
    {
        $ingreso->update($request->validated());
        $docs = $request->base64;

        if (!empty($docs)) {
            foreach ($docs as $doc) {
                $sa = IngresoDoc::create([
                    "name" => $doc['name'],
                    "ingreso_id" => $ingreso->id
                ]);
                $relativePath  = $this->saveDoc($doc['base64'], $sa->default_path_folder);
                $sa->update(['path' => $relativePath]);
            }
        }
        return $this->respond($ingreso);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ingreso $ingreso)
    {
        $ingreso->delete();
        return $this->respondSuccess();
    }

    public function getPerCliente(Cliente $cliente)
    {
        $ingresos = Ingreso::where('cliente_id', $cliente->id)->with('cliente','ingresoDocs')->get();

        return $this->respond($ingresos);
    }
}
