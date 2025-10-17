<?php

namespace App\Http\Controllers\Intranet;

use App\Models\Empleado;
use Illuminate\Http\Request;
use App\Imports\ClientesImport;
use App\Models\Intranet\Tactic;
use App\Models\Intranet\Cliente;
use App\Models\Intranet\StateEntity;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Intranet\Segmentation;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\Classification;
use App\Models\Intranet\TechnologicalCapability;
use App\Models\Intranet\ConstructionClassification;
use App\Http\Requests\Intranet\Cliente\PutClienteRequest;
use App\Http\Requests\Intranet\Cliente\StoreClienteRequest;

class ClienteController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $user = Auth::user(); // Usuario autenticado

        $clientes = Cliente::query()
            ->when(!$user->hasRole('Credito'), function ($query) use ($user) {
                // Si NO tiene rol "credito", filtra solo los clientes relacionados con su empleado
                $query->whereHas('empleados', function ($q) use ($user) {
                    $q->where('empleados.id', $user->empleado->id);
                });
            })
            ->filter($filters)
            ->with('stateEntity', 'town', 'classification', 'segmentation', 'tactic', 'constructionClassification')
            ->paginate(10);

        return $this->respond($clientes);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClienteRequest $request)
    {
        $user = auth()->user();

        // Crear el cliente con los datos validados
        $cliente = Cliente::create($request->validated());

        // Validar roles del usuario
        if (! $user->hasAnyRole(['Credito', 'Intranet.sales'])) {
            // Si el usuario no tiene esos roles, agregamos la relación con su empleado
            if ($user->empleado) {
                // Asocia el empleado al cliente en la tabla pivote
                $cliente->empleados()->attach($user->empleado->id);
            }
        }

        return $this->respondCreated($cliente);
    }

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        return $this->respond($cliente);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutClienteRequest $request, Cliente $cliente)
    {
        $user = auth()->user();

        // Actualizar cliente
        $cliente->update($request->validated());

        // Validar roles del usuario
        if (! $user->hasAnyRole(['Credito', 'Intranet.sales']) && $user->empleado) {
            // Agrega la relación empleado-cliente si no existe, sin duplicar
            $cliente->empleados()->syncWithoutDetaching($user->empleado->id);
        }

        return $this->respond($cliente);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return $this->respondSuccess();
    }

    public function getOptions()
    {
        $data = [
            'states' => StateEntity::all(),
            'classifications' => Classification::all(),
            'segmentations' => Segmentation::all(),
            'tactics' => Tactic::all(),
            'constructionClassifications' => ConstructionClassification::all(),
        ];

        return $this->respond($data);
    }

    public function insetExcel(Request $request)
    {
        // Validar que el archivo sea un archivo .xlsx
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        // Obtener el archivo cargado
        $file = $request->file('file');

        // Importar el archivo .xlsx usando el importador
        Excel::import(new ClientesImport, $file);

        return $this->respond("Clientes importados con exito");
    }

    // ClienteController.php
    public function getCapTech(Cliente $cliente)
    {
        // Obtén los IDs de las capacidades tecnológicas asociadas al cliente
        $associatedCapabilityIds = $cliente->technologicalCapabilities->pluck('id');

        // Obtén todas las capacidades tecnológicas
        $allCapabilities = TechnologicalCapability::all();

        // Prepara la respuesta con los IDs asociados y todas las capacidades tecnológicas
        $data = [
            'currentClassTech' => $cliente->currentClassTech,
            'capabilities' => $associatedCapabilityIds,
            'capTech' => $allCapabilities
        ];

        return $this->respond($data);
    }


    public function addCapTech(Request $request, Cliente $cliente)
    {
        // Valida el request para asegurarte de que se están enviando IDs válidos
        $validated = $request->validate([
            'capabilities' => 'nullable|array',
            'capabilities.*' => 'nullable|exists:technological_capabilities,id',
        ]);

        // Obtén el array de IDs desde la solicitud
        $capabilityIds = $validated['capabilities'];

        // Sincroniza los IDs en la tabla pivote
        $cliente->technologicalCapabilities()->sync($capabilityIds);

        return $this->respondSuccess();
    }

    /**
     * Busca un cliente por su RFC.
     *
     * @param  string  $rfc
     * @return \Illuminate\Http\JsonResponse
     */
    public function findByRfc(string $rfc)
    {
        $user = auth()->user();
        $empleado = $user->empleado;

        // Buscar cliente con sus relaciones necesarias
        $cliente = Cliente::with([
            'stateEntity',
            'town',
            'classification',
            'segmentation',
            'tactic',
            'constructionClassification',
            'empleados:id'
        ])->where('rfc', $rfc)->first();

        // 1️⃣ Cliente no encontrado
        if (! $cliente) {
            return response()->json([
                'message' => 'Cliente no encontrado con el RFC proporcionado.',
            ], 404);
        }

        // 2️⃣ Si el usuario tiene el rol "Credito", puede acceder directamente
        if ($user->hasRole('Credito')) {
            return response()->json(['cliente' => $cliente]);
        }

        // 3️⃣ Si no tiene el rol, validamos la relación empleado-cliente
        if (! $empleado) {
            return response()->json([
                'message' => 'El usuario no tiene un empleado asociado.',
            ], 403);
        }

        // 4️⃣ Si el cliente no tiene empleados asociados, puede verlo cualquiera
        if ($cliente->empleados->isEmpty()) {
            return response()->json(['cliente' => $cliente]);
        }

        // 5️⃣ Si tiene empleados asociados, validar que el empleado autenticado esté relacionado
        $isAsociado = $cliente->empleados()
            ->where('empleado_id', $empleado->id)
            ->exists();

        if (! $isAsociado) {
            return response()->json([
                'message' => 'Cliente ya asociado a otro empleado.',
            ], 403);
        }

        // ✅ Todo correcto
        return response()->json(['cliente' => $cliente]);
    }

    public function getEmpleados(Request $request)
    {
        $filters = $request->all();
        $empleados = Empleado::filter($filters)
            ->with('clientes')
            ->orderBy('apellido_paterno', 'asc')
            ->get();

        return $this->respond($empleados);
    }

    public function getClientes(Request $request)
    {
        $filters = $request->all();
        $clientes = Cliente::filter($filters)
            ->with('empleados')
            ->orderBy('nombre', 'asc')
            ->get();

        return $this->respond($clientes);
    }

    public function syncEmpleadoClientes(Request $request)
    {
        $empleadoId = $request->input('selectedEmpleado.id');
        $clienteIds = $request->input('selectedCustomers', []);

        // Validar que existan
        if (!$empleadoId || empty($clienteIds)) {
            return response()->json(['message' => 'Datos incompletos'], 400);
        }

        $empleado = Empleado::find($empleadoId);

        if (!$empleado) {
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }

        // Sincroniza la relación (actualiza la tabla pivote)
        $empleado->clientes()->sync($clienteIds);

        return response()->json([
            'message' => 'Relación empleado-clientes actualizada correctamente',
            'empleado' => $empleado->load('clientes')
        ], 200);
    }
}
