<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Cliente;
use App\Exports\ClientesNTExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\ClienteTechnology;
use App\Http\Requests\Intranet\ClienteTechnology\StoreClienteTechnologyRequest;
use App\Models\Intranet\NuevaTecnologia;

class ClienteTechnologyController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(ClienteTechnology::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClienteTechnologyRequest $request)
    {
        $clienteTechnology = ClienteTechnology::create($request->validated());
        return $this->respondCreated($clienteTechnology);
    }

    /**
     * Display the specified resource.
     */
    public function show(ClienteTechnology $clienteTechnology)
    {
        return $this->respond($clienteTechnology);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreClienteTechnologyRequest $request, ClienteTechnology $clienteTechnology)
    {
        $clienteTechnology->update($request->validated());
        return $this->respond($clienteTechnology);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClienteTechnology $clienteTechnology)
    {
        $clienteTechnology->delete();
        return $this->respondSuccess();
    }

    public function getPerCliente(Cliente $cliente)
    {
        $machine = ClienteTechnology::where('cliente_id', $cliente->id)
            ->with('nuevaTecnologia')
            ->get();
        return $this->respond($machine);
    }

    public function getOptions()
    {
        $data = [
            'nt' => NuevaTecnologia::all()
        ];

        return $this->respond($data);
    }

    public function getClientesNT(Request $request)
    {
        $filters = $request->all();
        $clientes = Cliente::where(function ($query) {
            $query->whereHas('clienteTechnology')
                ->orWhereHas('distribucion');
        })
            ->filter($filters)
            ->with('stateEntity', 'town')
            ->paginate(10);

        return $this->respond($clientes);
    }

    public function getClientesNTxls(Request $request)
    {
        $filters = $request->all();
        $clientes = Cliente::where(function ($query) {
            $query->whereHas('clienteTechnology')
                ->orWhereHas('distribucion');
        })
            ->filter($filters)
            ->with('stateEntity', 'town')
            ->get();

        // Exportar a Excel en memoria
        $export = new ClientesNTExport($clientes);

        $data = $export->collection();

        // Verificar si no hay datos para exportar
        if ($data->isEmpty()) {
            return response()->json(['error' => 'No hay datos para exportar.']);
        }

        $fileContent = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

        // Convertir el contenido del archivo a Base64
        $base64 = base64_encode($fileContent);

        return response()->json([
            'file_name' => 'clientes_export.xlsx',
            'file_base64' => $base64,
        ]);

        return $this->respond($clientes);
    }
}
