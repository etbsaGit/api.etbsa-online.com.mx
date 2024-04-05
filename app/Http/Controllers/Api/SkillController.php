<?php

namespace App\Http\Controllers\Api;

use App\Models\Skill;
use App\Models\Puesto;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Skill\PutRequest;
use App\Http\Requests\Skill\StoreRequest;

class SkillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Skill::with('puesto')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $skill = Skill::create($request->only(['name']));
        $puestos = $request->puestos;
        $skill->puesto()->sync($puestos);
        return response()->json($skill->load('puesto'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Skill $skill)
    {
        return response()->json($skill->load('puesto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, Skill $skill)
    {
        $skill->update($request->only(['name']));
        $puestos = $request->puestos;
        $skill->puesto()->sync($puestos);
        return response()->json($skill->load('puesto'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Skill $skill)
    {
        $skill->delete();
        return response()->json("ok");
    }

    public function getPerPuesto(Puesto $puesto)
    {
        $skills = $puesto->skill()->get();

        return response()->json($skills);
    }
}
