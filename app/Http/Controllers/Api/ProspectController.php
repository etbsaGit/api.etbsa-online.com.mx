<?php

namespace App\Http\Controllers\Api;

use App\Models\Empleado;
use App\Models\Prospect;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Prospect\PutRequest;
use App\Http\Requests\Prospect\StoreRequest;

class ProspectController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $user = Auth::user();
        $sc = Sucursal::where('nombre', 'Corporativo')->first();

        if ($user->hasRole('Admin') || ($sc && $user->empleado->sucursal_id == $sc->id)) {
            $prospect = Prospect::filter($filters)
                ->with(['empleado','vendedor'])
                ->paginate(10);
        } else {
            $prospect = Prospect::filter($filters)
                ->where('empleado_id', $user->empleado->id)
                ->with(['empleado','vendedor'])
                ->paginate(10);
        }

        return $this->respond($prospect);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $prospect = Prospect::create($request->validated());

        return $this->respondCreated($prospect);
    }

    /**
     * Display the specified resource.
     */
    public function show(Prospect $prospect)
    {
        return $this->respond($prospect->load('empleado','vendedor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, Prospect $prospect)
    {
        $prospect->update($request->validated());

        return $this->respond($prospect);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prospect $prospect)
    {
        $prospect->delete();

        return $this->respondSuccess();
    }

    public function getforms()
    {
        $data = [
            'empleados' => Empleado::where('estatus_id', 5)
                ->whereHas('puesto', function ($query) {
                    $query->where('nombre', 'like', '%gerente%');
                })
                ->orderBy('apellido_paterno')
                ->get(),
        ];

        return $this->respond($data);
    }

    public function getAllSubordinates(Empleado $empleado)
    {
        $subordinates = Empleado::where('sucursal_id', $empleado->sucursal_id)
            ->orderBy('apellido_paterno')
            ->get();

        return $this->respond(['subordinates' => $subordinates]);
    }
}
