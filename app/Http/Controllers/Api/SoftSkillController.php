<?php

namespace App\Http\Controllers\Api;

use App\Models\SoftSkill;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\SoftSkill\StoreRequest;
use App\Models\SoftSkillNivel;

class SoftSkillController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        $softSkill = SoftSkill::filter($filters)
            ->with('niveles')
            ->paginate(10);
        return $this->respond($softSkill);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $softSkill = SoftSkill::create($request->validated());

        // Creamos las relaciones
        if ($request->has('niveles')) {
            foreach ($request->input('niveles') as $relacion) {
                // Asignamos el id de la declaración recién creada
                $relacion['soft_skill_id'] = $softSkill->id;
                SoftSkillNivel::create($relacion);
            }
        }

        return $this->respondCreated($softSkill->load('niveles'));
    }

    /**
     * Display the specified resource.
     */
    public function show(SoftSkill $softSkill)
    {
        $this->respond($softSkill);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, SoftSkill $softSkill)
    {
        $softSkill->update($request->validated());

        if ($request->has('niveles')) {
            foreach ($request->input('niveles') as $relacionData) {
                if (!empty($relacionData['id'])) {
                    // Si tiene ID, la buscamos y actualizamos
                    $relacion = SoftSkillNivel::find($relacionData['id']);
                    if ($relacion) {
                        $relacion->update($relacionData);
                    }
                } else {
                    // Si no tiene ID, la creamos vinculada a esta declaración
                    $relacionData['soft_skill_id'] = $softSkill->id;
                    SoftSkillNivel::create($relacionData);
                }
            }
        }
        return $this->respond($softSkill);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SoftSkill $softSkill)
    {
        $softSkill->delete();
        return $this->respondSuccess();
    }
}
