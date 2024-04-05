<?php

namespace App\Http\Controllers\Api;

use App\Models\Puesto;
use App\Models\SkillRating;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\SkillRating\PutRequest;
use App\Http\Requests\SkillRating\StoreRequest;
use App\Models\Empleado;

class SkillRaitngController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $skillRating = $request->skillRating;
        $empleado_id = $request->empleado_id;

        foreach ($skillRating as $rating) {
            SkillRating::create([
                'empleado_id' => $empleado_id,
                'skill_id' => $rating['skill_id'],
                'rating' => $rating['rating']
            ]);
        }

        return response()->json(['message' => 'Registro creado correctamente'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(SkillRating $skillRating)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, SkillRating $skillRating)
    {
        $skillRating->update($request->validated());
        return response()->json($skillRating);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SkillRating $skillRating)
    {
        //
    }

    public function getPerEmpleado(Empleado $empleado)
    {
        $skills = $empleado->puesto->skill()->get();
        foreach ($skills as $skill) {
            $existingSkillRating = SkillRating::where('empleado_id', $empleado->id)
                ->where('skill_id', $skill->id)
                ->exists();
            if (!$existingSkillRating) {
                SkillRating::create([
                    'empleado_id' => $empleado->id,
                    'skill_id' => $skill->id,
                    'rating' => 0,
                ]);
            }
        }
        $updatedSkillRating = $empleado->skillRating()->with('skill')->get();
        return response()->json($updatedSkillRating);
    }

    public function saveSkillRating(Request $request)
    {
        // Obtener los datos de la solicitud
        $data = $request->json()->all();

        foreach ($data as $skillRatingData) {
            $this->putSkillRating($skillRatingData);
        }

        return response()->json($data);
    }

    private function putSkillRating(array $skillRatingData)
    {
        // Obtener el id del skillRatingData
        $id = $skillRatingData['id'];

        // Buscar el SkillRating por su id
        $skillRating = SkillRating::findOrFail($id);

        // Validar y actualizar los datos del SkillRating
        $skillRating->update($skillRatingData);

        return response()->json($skillRating);
    }
}
