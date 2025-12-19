<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Intranet\InvCategory;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\InvCategory\StoreRequest;
use App\Models\Estatus;
use App\Models\Intranet\InvGroup;

class InvCategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $invCategories = InvCategory::filter($filters)->with('status','invGroup')->paginate(10);
        return $this->respond($invCategories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $invCategory = InvCategory::create($request->validated());
        return $this->respondCreated($invCategory);
    }

    /**
     * Display the specified resource.
     */
    public function show(InvCategory $invCategory)
    {
        return $this->respond($invCategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, InvCategory $invCategory)
    {
        $invCategory->update($request->validated());
        return $this->respond($invCategory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvCategory $invCategory)
    {
        $invCategory->delete();
        return $this->respondSuccess();
    }

public function getForms()
{
    $data = [
        'invGroups' => InvGroup::orderBy('name')->get(),
        'estatus' => Estatus::where('tipo_estatus', 'inv_category')
            ->orderBy('nombre')
            ->get(),
    ];

    return $this->respond($data);
}

}
