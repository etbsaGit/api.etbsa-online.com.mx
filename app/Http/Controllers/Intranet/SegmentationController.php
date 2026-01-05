<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Segmentation;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Segmentation\SegmentationRequest;

class SegmentationController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(
            Segmentation::filter($filters)->paginate(10),
            'Listado de segmentacion cargado correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SegmentationRequest $request)
    {
        $segmentation = Segmentation::create($request->validated());

        return $this->respondCreated(
            $segmentation,
            'Segmentacion registrada correctamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Segmentation $segmentation)
    {
        return $this->respond(
            $segmentation,
            'Detalle de segmentacion'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SegmentationRequest $request, Segmentation $segmentation)
    {
        $segmentation->update($request->validated());

        return $this->respond(
            $segmentation,
            'Segmentacion actualizada correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Segmentation $segmentation)
    {
        $segmentation->delete();
        return $this->respondSuccess(
            'Segmentacion eliminada correctamente'
        );
    }
}
