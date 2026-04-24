<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;
use App\Models\Intranet\Product;
use Illuminate\Http\Request;
use App\Http\Requests\Intranet\Products\ProductRequest;
use App\Http\Requests\Intranet\Tracking\TrackingProspectoRequest;
use App\Models\Intranet\ProductBrand;
use App\Models\Intranet\ProductCategory;
use App\Models\Intranet\ProductSubCategory;
use App\Models\Intranet\ProductSupplier;
use App\Models\Intranet\TrackingProspecto;
use App\Models\Prospect;
use App\Models\Sucursal;

class TrackingProspectoController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        $products = TrackingProspecto::with([])
            ->filter($filters)
            ->paginate(10);

        return $this->respond(
            $products,
            'Lista de prospectos cargada correctamente'
        );
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(TrackingProspectoRequest $request)
    {
        $prospect = TrackingProspecto::create($request->validated());
        return $this->respondCreated($prospect);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TrackingProspectoRequest $request, TrackingProspecto $tracking_prospect)
    {
        $tracking_prospect->update($request->validated());

        return $this->respond($tracking_prospect);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TrackingProspecto $prospect)
    {
        $prospect->delete();

        return $this->respondSuccess();
    }
}
