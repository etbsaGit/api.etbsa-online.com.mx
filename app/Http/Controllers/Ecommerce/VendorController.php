<?php

namespace App\Http\Controllers\Ecommerce;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\UploadableFile;
use App\Models\Ecommerce\Vendor;
use App\Contracts\VendorContract;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Ecommerce\StoreVendorRequest;
use App\Http\Requests\Ecommerce\UpdateVendorRequest;


class VendorController extends ApiController
{
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
        //
    }

    private function saveImage($base64, $defaultPathFolder)
    {
        // Check if image is valid base64 string
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            // Take out the base64 encoded text without mime type
            $image = substr($base64, strpos($base64, ',') + 1);
            // Get file extension
            $type = strtolower($type[1]); // jpg, png, gif

            // Check if file is an image
            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                throw new \Exception('invalid image type');
            }
            $image = str_replace(' ', '+', $image);
            $image = base64_decode($image);

            if ($image === false) {
                throw new \Exception('base64_decode failed');
            }
        } else {
            throw new \Exception('did not match data URI with image data');
        }

        $fileName = Str::random() . '.' . $type;
        $filePath = $defaultPathFolder . '/' . $fileName;

        // Guardar el archivo en AWS S3
        Storage::disk('s3')->put($filePath, $image);

        return $filePath;
    }

    private function getImageAsBase64($imageUrl)
    {
        // Obtener el contenido de la imagen de la URL en base64
        $imageContent = file_get_contents($imageUrl);
        $imageBase64 = base64_encode($imageContent);

        // Obtener el tipo de la imagen (por ejemplo, 'png')
        $imageType = pathinfo($imageUrl, PATHINFO_EXTENSION);

        // Construir el prefijo del formato de imagen
        $imagePrefix = 'data:image/' . $imageType . ';base64,';

        // Devolver la imagen en formato base64 con el prefijo
        return $imagePrefix . $imageBase64;
    }
}
