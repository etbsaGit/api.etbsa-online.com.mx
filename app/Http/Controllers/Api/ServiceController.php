<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Estatus;
use App\Models\Service;
use App\Models\Vehicle;
use App\Models\Empleado;
use App\Mail\ServiceEmail;
use Illuminate\Http\Request;
use App\Models\ServiceArchive;
use App\Traits\UploadableFile;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Service\StoreRequest;

class ServiceController extends ApiController
{
    use UploadableFile;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $user = auth()->user();

        $query = Service::filter($filters);

        // Caso: usuario con rol "service" y que tenga empleado
        if ($user->hasRole('service') && $user->empleado) {
            $sucursalId = $user->empleado->sucursal_id;

            $query->whereHas('vehicle', function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId);
            });
        }

        // Caso: usuario normal (que no es cc ni service)
        elseif (!$user->hasRole('cc')) {
            $filters['empleado_id'] = $user->empleado->id;
            $query->where('empleado_id', $filters['empleado_id']);
        }

        $service = $query
            ->with(
                'vehicle.estatus',
                'vehicle.sucursal',
                'vehicle.departamento',
                'empleado',
                'estatus',
                'archives'
            )
            ->latest()
            ->paginate(10);

        return $this->respond($service);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $service = Service::create($request->validated());
        $docs = $request->base64;

        if (!empty($docs)) {
            foreach ($docs as $doc) {
                $sa = ServiceArchive::create([
                    "name" => $doc['name'],
                    "service_id" => $service->id
                ]);
                $relativePath  = $this->saveDoc($doc['base64'], $sa->default_path_folder);
                $sa->update(['path' => $relativePath]);
            }
        }

        $service->load(['vehicle.estatus', 'empleado', 'estatus']);

        $emails = $this->getServiceRecipients($service);

        foreach ($emails as $email) {
            Mail::to($email)->send(new ServiceEmail($service));
        }

        return $this->respondCreated($service);
    }


    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        return $this->respond($service);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, Service $service)
    {
        $service->update($request->validated());
        $docs = $request->base64;

        if (!empty($docs)) {
            foreach ($docs as $doc) {
                $sa = ServiceArchive::create(["name" => $doc['name'], "service_id" => $service->id]);
                $relativePath  = $this->saveDoc($doc['base64'], $sa->default_path_folder);
                $updateData = ['path' => $relativePath];
                $sa->update($updateData);
            }
        }

        return $this->respond($service);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        $service->delete();

        return $this->respondSuccess();
    }

    public function getforms()
    {
        $user = auth()->user();

        if ($user->hasRole('cc')) {
            $empleados = Empleado::with('vehicle')
                ->whereHas('vehicle')
                ->orderBy('apellido_paterno', 'asc')
                ->get();
        } else {
            $empleados = Empleado::with('vehicle')
                ->where('sucursal_id', $user->empleado->sucursal_id)
                ->whereHas('vehicle')
                ->orderBy('apellido_paterno', 'asc')
                ->get();
        }

        $data = [
            'estatus'  => Estatus::where('tipo_estatus', 'service')->get(),
            'vehicles' => Vehicle::with('estatus')->get(),
            'empleados' => $empleados,
        ];

        return $this->respond($data);
    }



    public function changeEstatus(Service $service, int $status)
    {
        $service->status = $status;
        $service->save();

        $service->load(['vehicle.estatus', 'empleado', 'estatus']);

        $emails = $this->getServiceRecipients($service);

        foreach ($emails as $email) {
            Mail::to($email)->send(new ServiceEmail($service));
        }

        return $this->respondSuccess();
    }


    private function getServiceRecipients(Service $service): array
    {
        // Aseguramos cargar relaciones necesarias
        $service->loadMissing(['vehicle.sucursal', 'empleado.jefe_directo']);

        $emails = [];

        // Usuarios con rol "cc"
        $usersCC = User::all()->filter(fn($user) => $user->hasRole('cc'));
        $emails = array_merge($emails, $usersCC->pluck('email')->toArray());

        // Correo institucional del empleado
        if (!empty($service->empleado?->correo_institucional)) {
            $emails[] = $service->empleado->correo_institucional;
        }

        // Correo institucional del jefe directo
        if (!empty($service->empleado?->jefe_directo?->correo_institucional)) {
            $emails[] = $service->empleado->jefe_directo->correo_institucional;
        }

        // Usuario(s) con rol "service" de la misma sucursal del vehículo
        $usersService = User::whereHas('roles', fn($q) => $q->where('name', 'service'))
            ->whereHas(
                'empleado',
                fn($q) =>
                $q->where('sucursal_id', $service->vehicle->sucursal_id)
            )
            ->get();

        foreach ($usersService as $usr) {
            if (!empty($usr->email)) {
                $emails[] = $usr->email;
            }
        }

        return array_unique($emails);
    }
}
