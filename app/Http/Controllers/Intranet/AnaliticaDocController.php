<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Intranet\AnaliticaDoc;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;

class AnaliticaDocController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AnaliticaDoc $analiticaDoc)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AnaliticaDoc $analiticaDoc)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AnaliticaDoc $analiticaDoc)
    {
        // 1️⃣ Validar los datos entrantes
        $validatedData = $request->validate([
            'comentarios' => 'required|string',
        ]);

        // 2️⃣ Actualizar el modelo con los datos validados
        $analiticaDoc->update($validatedData);

        // 3️⃣ Retornar respuesta (JSON o redirect según el caso)
        return response()->json([
            'message' => 'Analítica actualizada correctamente.',
            'data' => $analiticaDoc
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AnaliticaDoc $analiticaDoc)
    {
        Storage::disk('s3')->delete($analiticaDoc->path);
        $analiticaDoc->delete();
        return $this->respondSuccess();
    }

    public function changeEstatus(AnaliticaDoc $analiticaDoc, int $status)
    {
        $analiticaDoc->status = $status;
        $analiticaDoc->save();

        return $this->respondSuccess();
    }
}
