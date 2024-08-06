<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Imports\ClientesImport;
use App\Models\Intranet\Tactic;
use App\Models\Intranet\Cliente;
use App\Models\Intranet\StateEntity;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Intranet\Segmentation;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\Classification;
use App\Models\Intranet\TechnologicalCapability;
use App\Models\Intranet\ConstructionClassification;
use App\Http\Requests\Intranet\Cliente\PutClienteRequest;
use App\Http\Requests\Intranet\Cliente\StoreClienteRequest;

class ClienteController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $clientes = Cliente::filterPage($filters)->with('stateEntity','town','classification','segmentation','technologicalCapability','tactic','constructionClassification')->paginate(10);
        return $this->respond($clientes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClienteRequest $request)
    {
        $cliente = Cliente::create($request->validated());
        return $this->respondCreated($cliente);
    }

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        return $this->respond($cliente);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutClienteRequest $request, Cliente $cliente)
    {
        $cliente->update($request->validated());
        return $this->respond($cliente);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return $this->respondSuccess();
    }

    public function getOptions()
    {
        $data = [
            'states' => StateEntity::all(),
            'classifications' => Classification::all(),
            'segmentations' => Segmentation::all(),
            'technologicalCapabilities' => TechnologicalCapability::all(),
            'tactics' => Tactic::all(),
            'constructionClassifications' => ConstructionClassification::all(),
        ];

        return $this->respond($data);
    }

    public function insetExcel(Request $request)
    {
        // Validar que el archivo sea un archivo .xlsx
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        // Obtener el archivo cargado
        $file = $request->file('file');

        // Importar el archivo .xlsx usando el importador
        Excel::import(new ClientesImport, $file);

        return $this->respond("Clientes importados con exito");

    }
}
