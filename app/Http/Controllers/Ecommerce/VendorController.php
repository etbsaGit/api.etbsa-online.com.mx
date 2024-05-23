<?php

namespace App\Http\Controllers\Ecommerce;

use App\Traits\UploadableFile;
use App\Models\Ecommerce\Vendor;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Ecommerce\StoreVendorRequest;
use App\Http\Requests\Ecommerce\UpdateVendorRequest;


class VendorController extends ApiController
{
    use UploadableFile;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Vendor::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVendorRequest $request)
    {

        $vendor = Vendor::create($request->only(['name','slug']));

        if (!is_null($request['base64'])) {
            $relativePath  = $this->saveImage($request['base64'], $vendor->default_path_folder);
            $request['base64'] = $relativePath;
            $updateData = ['logo' => $relativePath];
            $vendor->update($updateData);
        }
        return response()->json($vendor);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vendor $vendor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVendorRequest $request, Vendor $vendor)
    {

        $vendor->update($request->validated());

        if (!is_null($request['base64'])) {
            if ($vendor->logo) {
                Storage::disk('s3')->delete($vendor->logo);
            }
            $relativePath  = $this->saveImage($request['base64'], $vendor->default_path_folder);
            $request['base64'] = $relativePath;
            $updateData = ['logo' => $relativePath];
            $vendor->update($updateData);
        }

        return response()->json($vendor);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return $this->respondSuccess();
    }
}
