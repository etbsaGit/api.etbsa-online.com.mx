<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Sale;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Sale\PutSaleRequest;
use App\Http\Requests\Intranet\Sale\StoreSaleRequest;
use App\Http\Requests\Intranet\Sale\StoreValidatedSaleRequest;
use App\Models\Estatus;
use App\Models\Intranet\Cliente;

class SaleController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $sales = Sale::filterSale($filters)
            ->with('cliente', 'referencia', 'status')
            ->orderBy('date', 'desc') // Ordenar por 'date' de forma descendente
            ->paginate(10);

        return $this->respond($sales);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSaleRequest $request)
    {
        $sale = Sale::create($request->validated());
        return $this->respondCreated($sale);
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        return $this->respond($sale);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutSaleRequest $request, Sale $sale)
    {
        $sale->update($request->validated());
        return $this->respond($sale);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        $sale->delete();
        return $this->respondSuccess();
    }

    public function getOptions()
    {
        $data = [
            'clientes' => Cliente::all(),
            'statuses' => Estatus::where('tipo_estatus', 'sale')->get(),
        ];
        return $this->respond($data);
    }

    public function getForValidate()
    {
        $sales = Sale::whereNull('validated')
            ->whereNotNull('invoice')
            ->with('cliente', 'referencia', 'status')
            ->orderBy('date', 'desc')
            ->get();

        return $this->respond($sales);
    }

    public function postValidate(StoreValidatedSaleRequest $request)
    {
        // Obtener los datos validados
        $data = $request->validated();

        // Recorrer cada objeto en el array
        foreach ($data as $item) {
            // Encontrar el objeto en la base de datos por ID
            $sale = Sale::find($item['id']);

            // Verificar si el objeto existe
            if ($sale) {
                // Actualizar el feedback y validated
                $sale->feedback = $item['feedback'] ?? $sale->feedback; // Mantener el antiguo si es null
                $sale->validated = $item['validated']; // Actualizar validated (puede ser null)
                $sale->save(); // Guardar los cambios
            }
        }

        return $this->respondSuccess();
    }
}
