<?php

namespace App\Http\Controllers\Api;

use App\Models\Puesto;
use Illuminate\Http\Request;
use App\Exports\PuestosExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Puesto\PutRequest;
use App\Http\Requests\Puesto\StoreRequest;

class PuestoController extends ApiController
{
    public function index(Request $request)
    {
        $filters = $request->all();
        $puestos = Puesto::filter($filters)->paginate(10);
        return $this->respond($puestos);
    }

    public function store(StoreRequest $request)
    {
        return response()->json(Puesto::create($request->validated()));
    }

    public function show(Puesto $puesto)
    {
        return response()->json($puesto);
    }

    public function update(PutRequest $request, Puesto $puesto)
    {
        $puesto->update($request->validated());
        return response()->json($puesto);
    }

    public function destroy(Puesto $puesto)
    {
        $puesto->delete();
        return response()->json("ok");
    }

    public function export(Request $request)
    {
        // Recoger los filtros desde la solicitud
        $filters = $request->except(['search', 'page']);
        // Crear una instancia de la clase de exportación con los filtros
        $export = new PuestosExport($filters);

        // Obtener los datos para verificar si están vacíos
        $data = $export->collection();

        // Verificar si no hay datos para exportar
        if ($data->isEmpty()) {
            return response()->json(['error' => 'No hay datos para exportar.']);
        }

        // Exportar el archivo en formato XLSX con los filtros aplicados
        $fileContent = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

        // Convertir el contenido del archivo a Base64
        $base64 = base64_encode($fileContent);

        // Devolver la respuesta con el archivo en Base64
        return response()->json([
            'file_name' => 'puestos.xlsx',
            'file_base64' => $base64,
        ]);
    }
}
