<?php

namespace App\Http\Controllers\Ecommerce;

use App\Contracts\VendorContract;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Ecommerce\StoreVendorRequest;
use App\Http\Requests\Ecommerce\UpdateVendorRequest;
use App\Models\Ecommerce\Vendor;
use App\Traits\UploadableFile;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;


class VendorController extends ApiController
{
    use UploadableFile;

    private VendorContract $vendorRepository;

    public function __construct(VendorContract $vendorRepository)
    {
        $this->vendorRepository = $vendorRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = $this->vendorRepository->index();

        return $this->respond([
            'data' => $items,
            'message' => 'Recursos Encontrados'
        ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVendorRequest $request)
    {

        $payload = $request->validated();
        $vendor = $this->vendorRepository->createVendor($payload);

        return $this->respondCreated([
            "success" => true,
            "message" => "Vendor created successfully",
            "item" => $vendor
        ]);
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

        $payload = $request->validated();
        $vendor = $this->vendorRepository->updateVendor($vendor->id, $payload);

        return $this->respond([
            'success' => true,
            'message' => 'Vendor has been updated.',
            'updated' => $vendor
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendor $vendor)
    {
        //
    }

    public function deleteLogo(Vendor $vendor): bool
    {

        if (\Storage::disk('s3')->delete($vendor->storageLogo)) {
            $vendor->logo = null;
            $vendor->save();

            return true;
        }

        return false;
    }
}
