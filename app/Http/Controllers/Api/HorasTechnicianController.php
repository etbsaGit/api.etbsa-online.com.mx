<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\HorasTechnician;
use App\Http\Controllers\ApiController;
use App\Http\Requests\HorasTechnician\StoreHorasTechnicianRequest;

class HorasTechnicianController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(HorasTechnician::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHorasTechnicianRequest $request)
    {
        // Obtén los datos validados
        $data = $request->validated();

        // Verifica si ya existe un registro con la misma combinación de mes, año y tecnico_id
        $existingRecord = HorasTechnician::where('mes', $data['mes'])
            ->where('anio', $data['anio'])
            ->where('tecnico_id', $data['tecnico_id'])
            ->first();

        if ($existingRecord) {
            return $this->respondError('Ya se cargo el registro de ese tecnico del mes', 422);
        }

        // Crea el nuevo registro
        $horasTechnician = HorasTechnician::create($data);

        return $this->respondCreated($horasTechnician);
    }


    /**
     * Display the specified resource.
     */
    public function show(HorasTechnician $horasTechnician)
    {
        return $this->respond($horasTechnician);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreHorasTechnicianRequest $request, HorasTechnician $horasTechnician)
    {
        $data = $request->validated();

        $existingRecord = HorasTechnician::where('mes', $data['mes'])
            ->where('anio', $data['anio'])
            ->where('tecnico_id', $data['tecnico_id'])
            ->where('id', '!=', $horasTechnician->id) // Excluye el registro actual
            ->first();

        if ($existingRecord) {
            return $this->respondError('Ya se cargo el registro de ese tecnico del mes', 422);
        }

        $horasTechnician->update($data);

        return $this->respondCreated($horasTechnician);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HorasTechnician $horasTechnician)
    {
        $horasTechnician->delete();
        return $this->respondSuccess();
    }

    public function getPerTech($id, $anio)
    {
        $horasTechnicians = HorasTechnician::where('tecnico_id', $id)
            ->where('anio', $anio)
            ->get();

        return $this->respond($horasTechnicians);
    }
}
