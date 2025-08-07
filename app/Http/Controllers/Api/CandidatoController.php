<?php

namespace App\Http\Controllers\Api;

use App\Models\Candidato;
use Illuminate\Http\Request;
use App\Models\CandidatoNota;
use App\Traits\UploadableFile;
use App\Exports\CandidatosExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Candidato\PutRequest;
use App\Http\Requests\Candidato\StoreRequest;

class CandidatoController extends ApiController
{
    use UploadableFile;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $candidatos = Candidato::filter($filters)
            ->with(['requisicion.puesto', 'requisicion.sucursal', 'notas'])
            ->paginate(10);

        return $this->respond($candidatos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $candidato = Candidato::create($request->validated());

        if (!is_null($request['base64'])) {
            if ($candidato->cv) {
                Storage::disk('s3')->delete($candidato->cv);
            }
            $relativePath  = $this->saveImage($request['base64'], $candidato->default_path_folder);
            $request['base64'] = $relativePath;
            $updateData = ['cv' => $relativePath];
            $candidato->update($updateData);
        }

        // Guardar notas si vienen en el request
        if (isset($request['notas']) && is_array($request['notas'])) {
            foreach ($request['notas'] as $notaData) {
                if (!empty($notaData['nota'])) {
                    CandidatoNota::create([
                        'candidato_id' => $candidato->id,
                        'nota' => $notaData['nota'],
                    ]);
                }
            }
        }

        return $this->respondCreated($candidato);
    }

    /**
     * Display the specified resource.
     */
    public function show(Candidato $candidato)
    {
        return $this->respond($candidato->load('requisicion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, Candidato $candidato)
    {
        $candidato->update($request->validated());

        if (!is_null($request['base64'])) {
            if ($candidato->cv) {
                Storage::disk('s3')->delete($candidato->cv);
            }
            $relativePath  = $this->saveImage($request['base64'], $candidato->default_path_folder);
            $request['base64'] = $relativePath;
            $updateData = ['cv' => $relativePath];
            $candidato->update($updateData);
        }

        // Actualizar o crear notas
        if (isset($request['notas']) && is_array($request['notas'])) {
            foreach ($request['notas'] as $notaData) {
                if (!empty($notaData['nota'])) {
                    CandidatoNota::updateOrCreate(
                        [
                            'id' => $notaData['id'] ?? null,
                            'candidato_id' => $candidato->id,
                        ],
                        [
                            'nota' => $notaData['nota'],
                        ]
                    );
                }
            }
        }

        return $this->respond($candidato);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Candidato $candidato)
    {
        if ($candidato->cv) {
            Storage::disk('s3')->delete($candidato->cv);
        }
        $candidato->delete();
        return $this->respondSuccess();
    }

    public function getPerMonth($date)
    {
        // Convertir la fecha a un objeto Carbon para manipular fácilmente
        $carbonDate = \Carbon\Carbon::parse($date);
        $month = $carbonDate->month;

        $candidatos = Candidato::whereMonth('fecha_entrevista_1', $month)
            ->with(
                'requisicion.puesto',
                'requisicion.sucursal',
                'requisicion.linea',
                'requisicion.departamento',
                'requisicion.escolaridad',
                'requisicion.solicita',
                'requisicion.autoriza',
                'requisicion.voBo',
                'requisicion.recibe',
                'requisicion.competencias',
                'requisicion.herramientas',
            )
            ->get();

        return $this->respond($candidatos);
    }

    public function getXls()
    {
        $export = new CandidatosExport();

        $data = $export->collection();

        if ($data->isEmpty()) {
            return response()->json(['error' => 'No hay datos para exportar.']);
        }

        $fileContent = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        $base64 = base64_encode($fileContent);

        return response()->json([
            'file_name' => 'candidatos.xlsx',
            'file_base64' => $base64,
        ]);
    }
}
