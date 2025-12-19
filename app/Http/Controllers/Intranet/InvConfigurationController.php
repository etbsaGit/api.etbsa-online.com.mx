<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Models\Intranet\InvConfiguration;
use App\Http\Requests\Intranet\InvConfiguration\StoreRequest;
use App\Models\Intranet\InvCategory;

class InvConfigurationController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $invConfigurations = InvConfiguration::filter($filters)->with('invCategory.status','invCategory.invGroup')->paginate(10);
        return $this->respond($invConfigurations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $invConfiguration = InvConfiguration::create($request->validated());
        return $this->respondCreated($invConfiguration);
    }

    /**
     * Display the specified resource.
     */
    public function show(InvConfiguration $invConfiguration)
    {
        return $this->respond($invConfiguration);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, InvConfiguration $invConfiguration)
    {
        $invConfiguration->update($request->validated());
        return $this->respond($invConfiguration);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvConfiguration $invConfiguration)
    {
        $invConfiguration->delete();
        return $this->respondSuccess();
    }

    public function getForms()
    {
        $data = [
            'invCategories' => InvCategory::with('status', 'invGroup')
                ->orderBy('name', 'asc')
                ->get(),
        ];

        return $this->respond($data);
    }
}
