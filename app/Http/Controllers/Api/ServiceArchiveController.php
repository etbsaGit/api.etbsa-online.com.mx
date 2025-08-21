<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\ServiceArchive;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;

class ServiceArchiveController extends ApiController
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceArchive $serviceArchive)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceArchive $serviceArchive)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceArchive $serviceArchive)
    {
        Storage::disk('s3')->delete($serviceArchive->path);
        $serviceArchive->delete();
        return $this->respondSuccess();
    }

    public function changeEstatus(ServiceArchive $serviceArchive, int $status)
    {
        // 1. Cambiar el estatus del archivo
        $serviceArchive->status = $status;
        $serviceArchive->save();

        // 2. Si el nuevo estatus es 1, actualizar el service relacionado
        if ($status === 1) {
            $serviceArchive->service->update(['status' => 1]);
        }

        return $this->respondSuccess();
    }
}
