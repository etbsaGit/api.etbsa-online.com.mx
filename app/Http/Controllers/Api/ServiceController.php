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

        // si no tiene rol "cc", filtrar por empleado_id
        if (!$user->hasRole('cc')) {
            $filters['empleado_id'] = $user->empleado->id;
        }

        return $this->respond(Service::filter($filters)->with('vehicle.estatus', 'empleado', 'estatus', 'archives')->latest()->paginate(10));
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

        // Cargar relaciones necesarias
        $service->load(['vehicle.estatus', 'empleado', 'estatus']);

        // Obtener todos los usuarios con rol 'cc'
        $users = User::all()->filter(fn($user) => $user->hasRole('cc'));

        foreach ($users as $user) {
            Mail::to($user->email)->send(new ServiceEmail($service));
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
            $empleado = Empleado::with('vehicle')
                ->where('id', $user->empleado->id)
                ->first();

            $empleados = $empleado ? collect([$empleado]) : collect([]);
        }

        $data = [
            'estatus'  => Estatus::where('tipo_estatus', 'service')->get(),
            'vehicles' => Vehicle::all(),
            'empleados' => $empleados,
        ];

        return $this->respond($data);
    }



    public function changeEstatus(Service $service, int $status)
    {
        $service->status = $status;
        $service->save();

        // Cargar relaciones necesarias
        $service->load(['vehicle.estatus', 'empleado', 'estatus']);

        // Obtener todos los usuarios con rol 'cc'
        $email = $service->empleado->correo_institucional;

        Mail::to($email)->send(new ServiceEmail($service));

        return $this->respondSuccess();

    }
}
