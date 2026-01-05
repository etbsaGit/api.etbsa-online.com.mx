<?php

namespace App\Http\Controllers\Caja;

use Illuminate\Http\Request;
use App\Models\Caja\CajaCorte;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Caja\CajaCorte\PutRequest;
use App\Http\Requests\Caja\CajaCorte\StoreRequest;
use App\Models\Caja\CajaDetalleEfectivo;

class CajaCorteController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(CajaCorte::filter($filters)->with('sucursal')->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $cajaCorte = CajaCorte::create($request->validated());

            // Crear los pagos asociados
            foreach ($data['detalleEfectivo'] as $efectivo) {
                CajaDetalleEfectivo::create([
                    'corte_id' => $cajaCorte->id,
                    'cantidad' => $efectivo['cantidad'],
                    'denominacion_id' => $efectivo['denominacion_id'],
                ]);
            }
            DB::commit();

            return $this->respondCreated($cajaCorte);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->respond(['error' => $th], 500);
        }


        return $this->respondCreated($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(CajaCorte $cajaCorte)
    {
        return $this->respond($cajaCorte);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, CajaCorte $cajaCorte)
    {
        $cajaCorte->update($request->validated());
        return $this->respond($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CajaCorte $cajaCorte)
    {
        $cajaCorte->delete();
        return $this->respondSuccess();
    }
}
