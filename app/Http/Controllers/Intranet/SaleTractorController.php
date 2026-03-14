<?php

namespace App\Http\Controllers\Intranet;

use App\Models\Estatus;
use App\Models\Empleado;
use App\Models\Sucursal;
use App\Models;
use Illuminate\Http\Request;
use App\Models\Intranet\Sale;
use App\Models\Intranet\SaleTractor;
use App\Models\Intranet\Cliente;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Sale\SaleRequest;
use App\Http\Requests\Intranet\Sale\StoreValidatedSaleRequest;

class SaleTractorController extends ApiController
{
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
        $sales = SaleTractor::filter($filters)
            ->with('cliente','vendedor', 'sucursal', 'estatus')
            ->orderBy('fecha', 'desc') // Ordenar por 'date' de forma descendente
            ->paginate(10);

        return $this->respond($sales, 'Listado de pedidos cargado correctamente');
    }
}
