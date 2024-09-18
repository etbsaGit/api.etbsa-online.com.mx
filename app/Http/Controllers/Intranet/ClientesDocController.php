<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Traits\UploadableFile;
use App\Models\Intranet\Cliente;
use App\Models\Intranet\ClientesDoc;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Intranet\ClientesDoc\StoreClientesDocRequest;

class ClientesDocController extends ApiController
{
    use UploadableFile;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientesDocRequest $request)
    {
        $clientesDoc = ClientesDoc::create($request->validated());
        $relativePath  = $this->saveDoc($request['base64'], $clientesDoc->default_path_folder);
        $updateData = ['path' => $relativePath];
        $clientesDoc->update($updateData);
        return $this->respond($clientesDoc);
    }

    /**
     * Display the specified resource.
     */
    public function show(ClientesDoc $clientesDoc)
    {
        return $this->respond($clientesDoc);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreClientesDocRequest $request, ClientesDoc $clientesDoc)
    {
        $clientesDoc->update($request->validated());

        if (!is_null($request['base64'])) {
            if ($clientesDoc->path) {
                Storage::disk('s3')->delete($clientesDoc->path);
            }
            $relativePath  = $this->saveDoc($request['base64'], $clientesDoc->default_path_folder);
            $updateData = ['path' => $relativePath];
            $clientesDoc->update($updateData);
        }

        return $this->respond($clientesDoc);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClientesDoc $clientesDoc)
    {
        Storage::disk('s3')->delete($clientesDoc->path);
        $clientesDoc->delete();
        return $this->respondSuccess();
    }

    public function getPerCliente(Cliente $cliente)
    {
        $docs = ClientesDoc::where('cliente_id', $cliente->id)
            ->with('status')
            ->orderBy('updated_at', 'desc')
            ->get();

        return $this->respond($docs);
    }
}
