<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Town;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Town\PutTownRequest;
use App\Http\Requests\Intranet\Town\StoreTownRequest;

class TownController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Town::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTownRequest $request)
    {
        $town = Town::create($request->validated());
        return $this->respondCreated($town);
    }

    /**
     * Display the specified resource.
     */
    public function show(Town $town)
    {
        return $this->respond($town);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutTownRequest $request, Town $town)
    {
        $town->update($request->validated());
        return $this->respond($town);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Town $town)
    {
        $town->delete();
        return $this->respondSuccess();
    }

    public function getPerState($id)
    {
        $towns = Town::where('state_entity_id', $id)->get();
        return $this->respond($towns);
    }
}
