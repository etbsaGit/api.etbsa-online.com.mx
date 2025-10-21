<?php

namespace App\Http\Controllers\Intranet;

use App\Models\Estatus;
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
        // Si existe un path válido, borrar del S3
        if (!empty($clientesDoc->path)) {
            Storage::disk('s3')->delete($clientesDoc->path);
        }

        // Borrar el registro de la base de datos
        $clientesDoc->delete();

        return $this->respondSuccess();
    }


    public function getPerCliente(Cliente $cliente)
    {
        // 1. Definir los nombres de documentos requeridos si el cliente es FÍSICO
        $nombresFisico = [
            'ine',
            'curp',
            'comprobante de estado civil',
            'comprobante de domicilio',
            'estado de cuenta',
            'comprobante de situacion fiscal',
            'declaracion anual de hacienda',
            'cotizacion',
            'Carta buro',
            'Aviso de privacidad'
        ];

        $nombresMoral = [
            'acta constitutiva',
            'ultima carta de modificacion',
            'informacion financiera',
            'declaracion anual de hacienda',
            'estado de cuenta',
            'comprobante de domicilio',
            'comprobante de estado civil',
            'relacion patrimonial',
            'ine',
            'curp',
            'comprobante de situacion fiscal',
            'organigrama',
            'cotizacion',
            'Carta buro',
            'Aviso de privacidad'
        ];

        // 2. Obtener los estatus dependiendo del tipo de cliente
        $estatusTypeDocsQuery = Estatus::where('tipo_estatus', 'TypeDocs');

        if (strtolower($cliente->tipo) === 'fisica') {
            // Solo los que coincidan con los nombres definidos
            $estatusTypeDocsQuery->whereIn('nombre', $nombresFisico);
        }

        if (strtolower($cliente->tipo) === 'moral') {
            // Solo los que coincidan con los nombres definidos
            $estatusTypeDocsQuery->whereIn('nombre', $nombresMoral);
        }

        $estatusTypeDocs = $estatusTypeDocsQuery->get();

        // 3. Crear los ClientesDoc faltantes
        foreach ($estatusTypeDocs as $estatus) {
            $existe = ClientesDoc::where('cliente_id', $cliente->id)
                ->where('status_id', $estatus->id)
                ->exists();

            if (! $existe) {
                ClientesDoc::create([
                    'cliente_id' => $cliente->id,
                    'status_id'  => $estatus->id,
                ]);
            }
        }

        // 4. Obtener todos los documentos del cliente con su relación `status`
        $docs = ClientesDoc::where('cliente_id', $cliente->id)
            ->with('status')
            ->orderBy('updated_at', 'desc')
            ->get();

        // 5. Retornar la respuesta
        return $this->respond($docs);
    }
}
