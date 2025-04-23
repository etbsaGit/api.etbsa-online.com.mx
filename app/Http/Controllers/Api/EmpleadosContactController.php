<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\EmpleadosContact;
use App\Http\Controllers\ApiController;
use App\Http\Requests\EmpleadosContact\PutRequest;
use App\Http\Requests\EmpleadosContact\StoreRequest;


class EmpleadosContactController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(EmpleadosContact::filter($filters)->with('kinship')->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $empleadosContact = EmpleadosContact::create($request->validated());

        return $this->respondCreated($empleadosContact);
    }

    /**
     * Display the specified resource.
     */
    public function show(EmpleadosContact $empleadosContact)
    {
        return $this->respond($empleadosContact);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, EmpleadosContact $empleadosContact)
    {
        $empleadosContact->update($request->validated());
        return $this->respond($empleadosContact);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmpleadosContact $empleadosContact)
    {
        $empleadosContact->delete();
        return $this->respondSuccess();
    }
}
