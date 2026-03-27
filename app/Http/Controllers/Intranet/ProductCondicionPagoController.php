<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Products\ProductCondicionPagoRequest;
use App\Models\Intranet\ProductCategory;
use App\Models\Intranet\ProductCondicionPago;
use Illuminate\Http\Request;

class ProductCondicionPagoController extends ApiController
{
    public function index(Request $request)
    {
        $filters = $request->all();

        $condiciones = ProductCondicionPago::with([
            'categorias'
        ])
            ->filter($filters)
            ->paginate(10);

        return $this->respond(
            $condiciones,
            'Lista de condiciones de pago cargada correctamente'
        );
    }

    public function store(ProductCondicionPagoRequest $request)
    {
        $condicion = ProductCondicionPago::create([
            'name' => $request->name
        ]);

        return $this->respondCreated(
            $condicion,
            'Condición creada correctamente'
        );
    }

    public function show(ProductCondicionPago $condicion)
    {
        $condicion->load(['categorias']);
        return $this->respond(
            $condicion,
            'Detalle de la condición de pago'
        );
    }

    public function update(ProductCondicionPagoRequest $request, $id)
    {
        $condicion = ProductCondicionPago::findOrFail($id);

        $condicion->update([
            'name' => $request->name
        ]);

        return $this->respond(
            $condicion,
            'Condición de pago actualizada correctamente'
        );
    }

    public function destroy($id)
    {
        $condicion = ProductCondicionPago::findOrFail($id);

        $condicion->categorias()->detach();
        $condicion->delete();

        return $this->respondSuccess(
            'Condición de pago eliminada correctamente'
        );
    }

    public function getOptions()
    {
        $data = [
            'condiciones' => ProductCondicionPago::all(),
        ];
        return $this->respond($data);
    }
}
