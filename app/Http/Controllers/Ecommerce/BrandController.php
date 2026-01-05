<?php

namespace App\Http\Controllers\Ecommerce;

use App\Traits\UploadableFile;
use App\Models\Ecommerce\Brand;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Ecommerce\StoreBrandRequest;
use App\Http\Requests\Ecommerce\UpdateBrandRequest;

class BrandController extends ApiController
{
    use UploadableFile;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Brand::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBrandRequest $request)
    {
        $brand = Brand::create($request->only(['name','slug']));

        if (!is_null($request['base64'])) {
            $relativePath  = $this->saveImage($request['base64'], $brand->default_path_folder);
            $request['base64'] = $relativePath;
            $updateData = ['logo' => $relativePath];
            $brand->update($updateData);
        }
        return $this->respond($brand);
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $brand->update($request->validated());

        if (!is_null($request['base64'])) {
            if ($brand->logo) {
                Storage::disk('s3')->delete($brand->logo);
            }
            $relativePath  = $this->saveImage($request['base64'], $brand->default_path_folder);
            $request['base64'] = $relativePath;
            $updateData = ['logo' => $relativePath];
            $brand->update($updateData);
        }

        return $this->respond($brand);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        $brand->delete();
        return $this->respondSuccess();
    }

}
