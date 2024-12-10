<?php

namespace App\Http\Controllers\Api;

use App\Models\Estatus;
use App\Models\Empleado;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use App\Models\TechniciansInvoice;
use App\Http\Controllers\ApiController;
use App\Http\Requests\TechniciansInvoice\PutTechniciansInvoiceRequest;
use App\Http\Requests\TechniciansInvoice\StoreTechniciansInvoiceRequest;

class TechniciansInvoiceController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(TechniciansInvoice::with('tecnico', 'wo')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTechniciansInvoiceRequest $request)
    {
        $techniciansInvoice = TechniciansInvoice::create($request->validated());
        return $this->respondCreated($techniciansInvoice);
    }

    /**
     * Display the specified resource.
     */
    public function show(TechniciansInvoice $techniciansInvoice)
    {
        return $this->respond($techniciansInvoice);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutTechniciansInvoiceRequest $request, TechniciansInvoice $techniciansInvoice)
    {
        $techniciansInvoice->update($request->validated());
        return $this->respond($techniciansInvoice);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TechniciansInvoice $techniciansInvoice)
    {
        $techniciansInvoice->delete();
        return $this->respondSuccess();
    }

    public function getWoPerTech(Empleado $empleado)
    {
        // Obtener los IDs de los estatus deseados usando el modelo Estatus
        $estatusIds = Estatus::whereIn('nombre', ['En taller', 'En campo'])->pluck('id');

        // Consultar las WorkOrder que coincidan con el tecnico_id y los estatus_taller_id
        $wos = WorkOrder::where('tecnico_id', $empleado->id)
            ->whereIn('estatus_taller_id', $estatusIds)
            ->get();

        return $this->respond($wos);
    }


    public function getPerTech(Request $request)
    {
        $filters = $request->all();
        // Consultar las WorkOrder que coincidan con el tecnico_id y el estatus_taller_id
        $invoices = TechniciansInvoice::filter($filters)
            ->with('wo')
            ->orderBy('fecha', 'desc')
            ->paginate(10);

        return $this->respond($invoices);
    }
}
