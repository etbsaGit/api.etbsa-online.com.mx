<?php

namespace App\Http\Controllers\Intranet;

use App\Models\Estatus;
use App\Models\Empleado;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use App\Models\Intranet\Sale;
use App\Models\Intranet\Cliente;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Sale\PutSaleRequest;
use App\Http\Requests\Intranet\Sale\StoreSaleRequest;
use App\Http\Requests\Intranet\Sale\StoreValidatedSaleRequest;

class SaleController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        // Obtén el usuario autenticado
        $user = Auth::user();

        // Verifica si el usuario no es Admin
        if (!$user->hasRole('Admin')) {
            // Si no es Admin, establece el sucursal_id en el request a la sucursal del empleado
            $filters['sucursal_id'] = $user->empleado->sucursal_id;

            // Verifica si la sucursal_id corresponde al ID de 'Corporativo'
            $corporativoSucursalId = Sucursal::where('nombre', 'Corporativo')->value('id');

            if ($filters['sucursal_id'] === $corporativoSucursalId) {
                // Reemplaza con el ID correspondiente a 'Celaya'
                $celayaSucursalId = Sucursal::where('nombre', 'Celaya')->value('id');
                $filters['sucursal_id'] = $celayaSucursalId;
            }
        }

        // Filtra las ventas
        $sales = Sale::filter($filters)
            ->with('cliente', 'referencia', 'status', 'empleado', 'sucursal')
            ->orderBy('date', 'desc') // Ordenar por 'date' de forma descendente
            ->paginate(10);

        return $this->respond($sales);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSaleRequest $request)
    {
        $data = $request->validated();

        $sale = Sale::create($data);

        $empleadoId = $data['empleado_id'];
        $clienteId  = $data['cliente_id'];

        // Obtener instancias
        $empleado = Empleado::findOrFail($empleadoId);

        // asociar si no existe
        $empleado->clientes()->syncWithoutDetaching([$clienteId]);

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
        $activoStatusId = Estatus::where('nombre', 'Activo')->value('id');

        $data = [
            'clientes' => Cliente::all(),
            'statuses' => Estatus::where('tipo_estatus', 'sale')->get(),
            'empleados' => Empleado::where('estatus_id', $activoStatusId)->orderBy('apellido_paterno')->get(),
            'sucursales' => Sucursal::all()
        ];

        return $this->respond($data);
    }

    public function getForValidate()
    {
        $sales = Sale::whereNull('validated')
            ->whereNotNull('invoice')
            ->with('cliente', 'referencia', 'status', 'empleado', 'sucursal')
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
