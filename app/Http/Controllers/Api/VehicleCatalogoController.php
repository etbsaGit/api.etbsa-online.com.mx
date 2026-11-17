<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\VehiclesMarcas;
use App\Models\VehiclesModelos;
use App\Models\VehiclesTipos;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleCatalogoController extends ApiController
{
    private const MODEL_MAP = [
        'marcas' => VehiclesMarcas::class,
        'modelos' => VehiclesModelos::class,
        'tipos' => VehiclesTipos::class,
    ];

    /**
     * Resuelve la clase del modelo según el tipo de catálogo.
     */
    private function resolveModel(string $tipo): string
    {
        if (!isset(self::MODEL_MAP[$tipo])) {
            abort(404, 'Catálogo de vehículo no encontrado');
        }

        return self::MODEL_MAP[$tipo];
    }

    public function index(Request $request, string $tipo)
    {
        $modelClass = $this->resolveModel($tipo);
        $filters = $request->all();

        $items = $modelClass::filter($filters)
            ->withCount('vehicles')
            ->orderBy('nombre')
            ->paginate(10);

        return $this->respond($items);
    }

    public function store(Request $request, string $tipo)
    {
        $modelClass = $this->resolveModel($tipo);
        $table = (new $modelClass())->getTable();

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique($table, 'nombre')],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe un registro con ese nombre.',
        ]);

        $validated['nombre'] = trim($validated['nombre']);
        $item = $modelClass::create($validated);

        return $this->respondCreated($item, 'Registro creado correctamente');
    }

    public function update(Request $request, string $tipo, int $id)
    {
        $modelClass = $this->resolveModel($tipo);
        $table = (new $modelClass())->getTable();
        $item = $modelClass::findOrFail($id);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique($table, 'nombre')->ignore($item->id)],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe un registro con ese nombre.',
        ]);

        $validated['nombre'] = trim($validated['nombre']);
        $item->update($validated);

        return $this->respond($item, 'Registro actualizado correctamente');
    }

    public function destroy(string $tipo, int $id)
    {
        $modelClass = $this->resolveModel($tipo);
        $item = $modelClass::findOrFail($id);

        if ($item->vehicles()->exists()) {
            return $this->respondError(
                'No se puede eliminar este registro porque tiene vehículos asociados.',
                422
            );
        }

        $item->delete();

        return $this->respondSuccess('Registro eliminado correctamente');
    }
}
