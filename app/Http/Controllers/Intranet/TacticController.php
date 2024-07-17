<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Tactic;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Tactic\StoreTacticRequest;

class TacticController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Tactic::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTacticRequest $request)
    {
        $tactic = Tactic::create($request->validated());
        return $this->respondCreated($tactic);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tactic $tactic)
    {
        return $this->respond($tactic);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreTacticRequest $request, Tactic $tactic)
    {
        $tactic->update($request->validated());
        return $this->respond($tactic);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tactic $tactic)
    {
        $tactic->delete();
        return $this->respondSuccess();
    }
}
