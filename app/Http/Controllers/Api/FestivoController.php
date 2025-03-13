<?php

namespace App\Http\Controllers\Api;

use App\Models\Festivo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Festivo\PutRequest;
use App\Http\Requests\Festivo\StoreRequest;

class FestivoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $year = $request->input('year'); // Cambiado a una única variable en lugar de un array

        // Validar que se reciba exactamente un year
        if (!is_numeric($year) || strlen($year) !== 4) {
            return response()->json(['error' => 'Debes enviar un year válido en formato YYYY'], 400);
        }

        $festivos = Festivo::whereYear('fecha', $year)->get();

        return $this->respond($festivos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $festivo = Festivo::create($request->validated());
        return $this->respondCreated($festivo);
    }

    /**
     * Display the specified resource.
     */
    public function show(Festivo $festivo)
    {
        return $this->respond($festivo);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, Festivo $festivo)
    {
        $festivo->update($request->validated());
        return $this->respond($festivo);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Festivo $festivo)
    {
        $festivo->delete();
        return $this->respondSuccess();
    }

    public function getFecha($year)
    {
        $fechas = Festivo::whereYear('fecha', $year)
            ->orWhereYear('fecha', $year - 1)
            ->orWhereYear('fecha', $year + 1)
            ->pluck('fecha')
            ->toArray();

        return $this->respond($fechas);
    }
}
