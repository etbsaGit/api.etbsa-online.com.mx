<?php

namespace App\Http\Controllers\Api;

use App\Models\Empleado;
use App\Models\SoftSkill;
use Illuminate\Http\Request;
use App\Models\SoftSkillEmpleado;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\SoftSkillEmpleado\StoreRequest;

class SoftSkillEmpleadoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Empleado $empleado)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(SoftSkillEmpleado $softSkillEmpleado)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SoftSkillEmpleado $softSkillEmpleado)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SoftSkillEmpleado $softSkillEmpleado)
    {
        $softSkillEmpleado->delete();
        return $this->respondSuccess();
    }

    public function perEmployee(Empleado $empleado)
    {
        // Obtener todas las soft skills existentes
        $softSkills = SoftSkill::all();

        foreach ($softSkills as $softSkill) {
            // Crear solo si NO existe ya la relación
            SoftSkillEmpleado::firstOrCreate([
                'empleado_id'   => $empleado->id,
                'soft_skill_id' => $softSkill->id,
            ]);
        }

        $data = SoftSkillEmpleado::where('empleado_id', $empleado->id)
            ->with('softSkill', 'nivel') // si quieres incluir info de la soft skill
            ->get();

        return $this->respond($data);
    }

    public function getLevels()
    {
        $data = SoftSkill::with('niveles')->get();
        return $this->respond($data);
    }

    public function updateSkills(StoreRequest $request)
    {
        $skills = $request->validated()['skills'];

        foreach ($skills as $skillData) {

            $skill = SoftSkillEmpleado::findOrFail($skillData['id']);

            $skill->update([
                'definicion' => $skillData['definicion'] ?? null,
                'evidencia' => $skillData['evidencia'] ?? null,
                'soft_skill_nivel_id' => $skillData['soft_skill_nivel_id'] ?? null,
            ]);
        }

        return $this->respondSuccess();
    }
}
