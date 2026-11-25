<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Vehicle\StoreVehicleChecklistRequest;
use App\Mail\Vehicles\VehicleChecklistAlertaMail;
use App\Models\Empleado;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehiclesChecklist;
use App\Models\VehiclesChecklistDetalle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VehicleChecklistController extends ApiController
{
    public const REQUIRED_DOCUMENTS = [
        'Placa delantera',
        'Placa trasera',
        'Tarjeta de circulación',
        'Verificación',
    ];

    private const CHECKLIST_RELATIONS = [
        'vehicle.marca:id,nombre',
        'vehicle.modelo:id,nombre',
        'vehicle.tipo:id,nombre',
        'vehicle.sucursal:id,nombre',
        'vehicle.departamento:id,nombre',
        'vehicle.linea:id,nombre',
        'vehicle.responsable:id,nombre,segundo_nombre,apellido_paterno,apellido_materno,vehicle_id',
        'verificador:id,name,email',
        'verificador.empleado:id,user_id,nombre,segundo_nombre,apellido_paterno,apellido_materno',
        'detalles',
    ];

    /**
     * Determina si el usuario autenticado puede ver todas las revisiones
     * (rol 'Admin', rol 'vehicles.checklist' o permiso 'vehicles.checklist').
     */
    private function canViewAll(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        try {
            if ($user->hasRole('Admin') || $user->hasRole('vehicles.checklist')) {
                return true;
            }
        } catch (\Throwable $e) {
            // Ignorar si el rol no existe en BD
        }

        try {
            if ($user->hasPermissionTo('vehicles.checklist') || $user->can('vehicles.checklist')) {
                return true;
            }
        } catch (\Throwable $e) {
            // Ignorar si el permiso no existe en BD
        }

        // Respaldo verificando colecciones de roles y permisos
        $roleNames = $user->roles ? $user->roles->pluck('name')->toArray() : [];
        if (in_array('Admin', $roleNames, true) || in_array('vehicles.checklist', $roleNames, true)) {
            return true;
        }

        $permNames = $user->permissions ? $user->permissions->pluck('name')->toArray() : [];
        if (in_array('vehicles.checklist', $permNames, true)) {
            return true;
        }

        return false;
    }

    /**
     * Obtiene los IDs de las unidades asignadas al empleado del usuario autenticado
     * (tanto de la tabla pivote usuarios_unidades como de empleados.vehicle_id).
     */
    private function getUserAssignedVehicleIds(?User $user): array
    {
        if (!$user) {
            return [];
        }

        $empleado = $user->empleado;
        if (!$empleado) {
            return [];
        }

        $vehicleIds = $empleado->vehicles()->pluck('vehicles.id')->map(fn($id) => (int) $id)->toArray();
        if (!empty($empleado->vehicle_id) && !in_array((int) $empleado->vehicle_id, $vehicleIds, true)) {
            $vehicleIds[] = (int) $empleado->vehicle_id;
        }

        return array_values(array_unique($vehicleIds));
    }

    /**
     * Optimiza los appends de Empleado en las relaciones cargadas para evitar consultas N+1.
     */
    private function optimizeChecklistEmpleados($items): void
    {
        $collection = $items instanceof \Illuminate\Support\Collection ? $items : collect([$items]);
        $empAppends = ['nombreCompleto', 'apellidoCompleto'];

        foreach ($collection as $checklist) {
            if (!$checklist) {
                continue;
            }
            if ($checklist->relationLoaded('verificador') && $checklist->verificador) {
                if ($checklist->verificador->relationLoaded('empleado') && $checklist->verificador->empleado) {
                    $checklist->verificador->empleado->setAppends($empAppends);
                }
            }
            if ($checklist->relationLoaded('vehicle') && $checklist->vehicle) {
                if ($checklist->vehicle->relationLoaded('responsable') && $checklist->vehicle->responsable) {
                    $checklist->vehicle->responsable->setAppends($empAppends);
                }
            }
        }
    }

    /**
     * Lista paginada de revisiones de flotillas.
     * - Si el usuario tiene rol Admin o vehicles.checklist, lista todas las revisiones.
     * - De lo contrario, lista únicamente las revisiones realizadas por el usuario autenticado.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $canSeeAll = $this->canViewAll($user);

        $perPage = (int) $request->input('per_page', 15);
        if ($perPage <= 0) {
            $perPage = 15;
        }

        $filters = $request->all();

        $now = Carbon::now();
        $currentQuarter = (int) $now->quarter;
        $currentYear = (int) $now->year;

        $assignedVehicleIds = !$canSeeAll ? $this->getUserAssignedVehicleIds($user) : [];

        $basePendingQuery = Vehicle::where('activo', 1)
            ->whereDoesntHave('checklist', function ($q) use ($currentQuarter, $currentYear) {
                $q->where('trimestre_revision', $currentQuarter)
                    ->whereYear('fecha_revision', $currentYear);
            });

        if (!$canSeeAll) {
            if (empty($assignedVehicleIds)) {
                $basePendingQuery->whereRaw('1 = 0');
            } else {
                $basePendingQuery->whereIn('id', $assignedVehicleIds);
            }
        }

        $pendingCount = (clone $basePendingQuery)->count();

        if (!empty($filters['sin_revisar_trimestre'])) {
            $query = clone $basePendingQuery;

            if (!empty($filters['vehicle_id'])) {
                $query->where('id', (int) $filters['vehicle_id']);
            }

            if (!empty($filters['sucursal_id'])) {
                $query->where('sucursal_id', (int) $filters['sucursal_id']);
            }

            if (!empty($filters['search'])) {
                $search = trim($filters['search']);
                $query->where(function ($qVeh) use ($search) {
                    $qVeh->where('placas', 'LIKE', '%' . $search . '%')
                        ->orWhere('serie', 'LIKE', '%' . $search . '%')
                        ->orWhere('version', 'LIKE', '%' . $search . '%')
                        ->orWhereHas('marca', fn($qm) => $qm->where('nombre', 'LIKE', '%' . $search . '%'))
                        ->orWhereHas('modelo', fn($qmo) => $qmo->where('nombre', 'LIKE', '%' . $search . '%'))
                        ->orWhereHas('sucursal', fn($qs) => $qs->where('nombre', 'LIKE', '%' . $search . '%'));
                });
            }

            $paginatedVehicles = $query->with([
                'marca:id,nombre',
                'modelo:id,nombre',
                'tipo:id,nombre',
                'sucursal:id,nombre',
                'departamento:id,nombre',
                'linea:id,nombre',
                'responsable:id,nombre,segundo_nombre,apellido_paterno,apellido_materno,vehicle_id',
                'checklist' => function ($q) {
                    $q->with('verificador.empleado:id,user_id,nombre,segundo_nombre,apellido_paterno,apellido_materno')
                        ->orderBy('fecha_revision', 'desc')
                        ->orderBy('id', 'desc')
                        ->limit(1);
                },
            ])
                ->orderBy('placas', 'asc')
                ->paginate($perPage);

            $empAppends = ['nombreCompleto', 'apellidoCompleto'];
            $paginatedVehicles->getCollection()->transform(function ($v) use ($empAppends) {
                if ($v->relationLoaded('responsable') && $v->responsable) {
                    $v->responsable->setAppends($empAppends);
                }
                $ultimaRevision = $v->relationLoaded('checklist') ? $v->checklist->first() : null;
                if ($ultimaRevision && $ultimaRevision->relationLoaded('verificador') && $ultimaRevision->verificador) {
                    if ($ultimaRevision->verificador->relationLoaded('empleado') && $ultimaRevision->verificador->empleado) {
                        $ultimaRevision->verificador->empleado->setAppends($empAppends);
                    }
                }
                return [
                    'id' => 'pending_' . $v->id,
                    'is_pending_vehicle' => true,
                    'vehicle_id' => $v->id,
                    'vehicle' => $v,
                    'ultima_revision' => $ultimaRevision,
                ];
            });

            $response = $paginatedVehicles->toArray();
            $response['pending_current_quarter_count'] = $pendingCount;
            $response['trimestre_actual'] = $currentQuarter;
            $response['anio_actual'] = $currentYear;

            return $this->respond($response);
        }

        if (!$canSeeAll) {
            $filters['verificador_id'] = $user->id;
        }

        $paginated = VehiclesChecklist::filter($filters)
            ->with(self::CHECKLIST_RELATIONS)
            ->orderBy('fecha_revision', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        $this->optimizeChecklistEmpleados($paginated->getCollection());

        $response = $paginated->toArray();
        $response['pending_current_quarter_count'] = $pendingCount;
        $response['trimestre_actual'] = $currentQuarter;
        $response['anio_actual'] = $currentYear;

        return $this->respond($response);
    }

    /**
     * Catálogos para el formulario y filtros de Revisión de Flotillas.
     */
    public function getforms(Request $request)
    {
        $user = Auth::user();
        $canSeeAll = $this->canViewAll($user);

        $vehicles = Vehicle::where('activo', 1)
            ->with([
                'marca:id,nombre',
                'modelo:id,nombre',
                'tipo:id,nombre',
                'sucursal:id,nombre',
                'departamento:id,nombre',
                'linea:id,nombre',
                'responsable:id,nombre,segundo_nombre,apellido_paterno,apellido_materno,vehicle_id',
            ])
            ->orderBy('placas', 'asc')
            ->get();

        $empAppends = ['nombreCompleto', 'apellidoCompleto'];
        foreach ($vehicles as $v) {
            if ($v->relationLoaded('responsable') && $v->responsable) {
                $v->responsable->setAppends($empAppends);
            }
        }

        $verificadores = [];
        if ($canSeeAll) {
            $verificadorIds = VehiclesChecklist::whereNotNull('verificador_id')
                ->distinct()
                ->pluck('verificador_id');

            $verificadores = User::whereIn('id', $verificadorIds)
                ->with('empleado:id,user_id,nombre,segundo_nombre,apellido_paterno,apellido_materno')
                ->select('id', 'name', 'email')
                ->get()
                ->each(function ($u) use ($empAppends) {
                    if ($u->relationLoaded('empleado') && $u->empleado) {
                        $u->empleado->setAppends($empAppends);
                    }
                });
        }

        $now = Carbon::now();
        $currentQuarter = (int) $now->quarter;
        $currentYear = (int) $now->year;

        $reviewedThisQuarterIds = VehiclesChecklist::where('trimestre_revision', $currentQuarter)
            ->whereYear('fecha_revision', $currentYear)
            ->distinct()
            ->pluck('vehicle_id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        $reviewedLookup = array_flip($reviewedThisQuarterIds);
        foreach ($vehicles as $v) {
            $v->revisado_trimestre_actual = isset($reviewedLookup[(int) $v->id]);
        }

        if ($canSeeAll) {
            $pendingCountForUser = $vehicles->where('revisado_trimestre_actual', false)->count();
        } else {
            $assignedIds = $this->getUserAssignedVehicleIds($user);
            $pendingCountForUser = $vehicles
                ->whereIn('id', $assignedIds)
                ->where('revisado_trimestre_actual', false)
                ->count();
        }

        return $this->respond([
            'vehicles' => $vehicles,
            'sucursales' => Sucursal::orderBy('nombre')->get(['id', 'nombre']),
            'verificadores' => $verificadores,
            'documentos_checklist' => self::REQUIRED_DOCUMENTS,
            'can_view_all' => $canSeeAll,
            'fecha_actual' => $now->toDateString(),
            'trimestre_actual' => $currentQuarter,
            'anio_actual' => $currentYear,
            'pending_current_quarter_count' => $pendingCountForUser,
        ]);
    }

    /**
     * Registra una nueva revisión de vehículo y sus detalles de documentos en físico.
     * La fecha y el trimestre del año se asignan automáticamente en el servidor.
     */
    public function store(StoreVehicleChecklistRequest $request)
    {
        $validated = $request->validated();
        $hasMissing = collect($validated['detalles'])->contains(fn($det) => !(bool) $det['existencia']);

        DB::beginTransaction();
        try {
            $now = Carbon::now();
            $userId = Auth::id();

            $checklist = VehiclesChecklist::create([
                'vehicle_id' => (int) $validated['vehicle_id'],
                'verificador_id' => $userId,
                'fecha_revision' => $now->toDateString(),
                'trimestre_revision' => (int) $now->quarter,
                'notas' => !empty($validated['notas']) ? trim($validated['notas']) : null,
            ]);

            foreach ($validated['detalles'] as $det) {
                VehiclesChecklistDetalle::create([
                    'checklist_id' => $checklist->id,
                    'documento' => trim($det['documento']),
                    'existencia' => (bool) $det['existencia'],
                ]);
            }

            DB::commit();

            $checklist->load(self::CHECKLIST_RELATIONS);
            $this->optimizeChecklistEmpleados($checklist);

            if ($hasMissing) {
                $checklistId = $checklist->id;
                dispatch(function () use ($checklistId) {
                    try {
                        $this->sendMissingDocumentsAlertEmail($checklistId);
                    } catch (\Throwable $e) {
                        Log::error("Error enviando correo de alerta de revisión de flotilla #{$checklistId}: " . $e->getMessage());
                    }
                })->afterResponse();
            }

            return $this->respondCreated($checklist, 'Revisión de vehículo registrada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->respondError('Error al registrar la revisión: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Muestra el detalle de una revisión específica.
     */
    public function show($id)
    {
        $user = Auth::user();
        $checklist = VehiclesChecklist::with(self::CHECKLIST_RELATIONS)->findOrFail($id);

        if (!$this->canViewAll($user) && (int) $checklist->verificador_id !== (int) $user->id) {
            return $this->respondError('No tienes permiso para visualizar esta revisión.', 403);
        }

        $this->optimizeChecklistEmpleados($checklist);

        return $this->respond($checklist);
    }

    /**
     * Actualiza una revisión existente y su checklist de documentos.
     * Solo permitido para Admin o usuarios con rol/permiso vehicles.checklist.
     */
    public function update(StoreVehicleChecklistRequest $request, $id)
    {
        $user = Auth::user();
        if (!$this->canViewAll($user)) {
            return $this->respondError('No tienes permiso para modificar esta revisión.', 403);
        }

        $checklist = VehiclesChecklist::findOrFail($id);
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $checklist->update([
                'vehicle_id' => (int) $validated['vehicle_id'],
                'notas' => !empty($validated['notas']) ? trim($validated['notas']) : null,
            ]);

            $checklist->detalles()->delete();

            foreach ($validated['detalles'] as $det) {
                VehiclesChecklistDetalle::create([
                    'checklist_id' => $checklist->id,
                    'documento' => trim($det['documento']),
                    'existencia' => (bool) $det['existencia'],
                ]);
            }

            DB::commit();

            $checklist->load(self::CHECKLIST_RELATIONS);
            $this->optimizeChecklistEmpleados($checklist);

            return $this->respond($checklist, 'Revisión actualizada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->respondError('Error al actualizar la revisión: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Elimina una revisión y sus detalles.
     * Solo permitido para Admin o usuarios con rol/permiso vehicles.checklist.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (!$this->canViewAll($user)) {
            return $this->respondError('No tienes permiso para eliminar esta revisión.', 403);
        }

        $checklist = VehiclesChecklist::findOrFail($id);

        DB::beginTransaction();
        try {
            $checklist->detalles()->delete();
            $checklist->delete();
            DB::commit();

            return $this->respondSuccess('Revisión eliminada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->respondError('Error al eliminar la revisión: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Envía un correo de alerta al Director Administrativo y al Coordinador de compras
     * cuando en una revisión de flotilla hay al menos un documento o placa que no se tiene en físico.
     */
    protected function sendMissingDocumentsAlertEmail(int $checklistId): void
    {
        $checklist = VehiclesChecklist::with(self::CHECKLIST_RELATIONS)->find($checklistId);
        if (!$checklist) {
            return;
        }

        $hasMissing = $checklist->detalles && $checklist->detalles->contains(fn($det) => !(bool) $det->existencia);
        if (!$hasMissing) {
            return;
        }

        $dirAdmin = Empleado::whereHas('puesto', function ($q) {
            $q->whereRaw('LOWER(nombre) = ?', ['director administrativo']);
        })->whereHas('estatus', function ($q) {
            $q->where('nombre', 'Activo');
        })->first();

        $coordCompras = Empleado::whereHas('puesto', function ($q) {
            $q->whereRaw('LOWER(nombre) = ?', ['coordinador de compras']);
        })->whereHas('estatus', function ($q) {
            $q->where('nombre', 'Activo');
        })->first();

        $correoDir = optional($dirAdmin)->correo_institucional ?: optional($dirAdmin?->user)->email;
        // $correoDir = 'munozchristian@etbsa.com.mx';
        $correoCoord = optional($coordCompras)->correo_institucional ?: optional($coordCompras?->user)->email;
        // $correoCoord = null;

        $recipients = collect([$correoDir, $correoCoord])
            ->filter(fn($email) => !empty($email))
            ->map(fn($email) => trim($email))
            ->unique(fn($email) => strtolower($email))
            ->values();

        if ($recipients->isEmpty()) {
            Log::warning("No se encontraron correos de Director Administrativo ni Coordinador de compras para alerta de revisión #{$checklistId}.");
            return;
        }

        $primaryTo = $recipients->first();
        $ccList = $recipients->slice(1)->values()->all();

        $mail = Mail::to($primaryTo);
        if (!empty($ccList)) {
            $mail->cc($ccList);
        }

        $mail->send(new VehicleChecklistAlertaMail($checklist));
    }
}
