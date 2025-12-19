<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\InvGroup;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\InvGroup\StoreRequest;

class InvGroupController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $invGroups = InvGroup::filter($filters)->paginate(10);
        return $this->respond($invGroups);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $invGroup = InvGroup::create($request->validated());
        return $this->respondCreated($invGroup);
    }

    /**
     * Display the specified resource.
     */
    public function show(InvGroup $invGroup)
    {
        return $this->respond($invGroup);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, InvGroup $invGroup)
    {
        $invGroup->update($request->validated());
        return $this->respond($invGroup);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvGroup $invGroup)
    {
        $invGroup->delete();
        return $this->respondSuccess();
    }
}
