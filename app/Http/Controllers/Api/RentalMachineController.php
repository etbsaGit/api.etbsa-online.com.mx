<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\RentalMachine;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\RentalMachine\PutRentalMachineRequest;
use App\Http\Requests\RentalMachine\StoreRentalMachineRequest;
use App\Models\Empleado;
use App\Models\Intranet\Cliente;
use App\Traits\UploadableFile;

class RentalMachineController extends ApiController
{
    use UploadableFile;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(RentalMachine::filter($filters)->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRentalMachineRequest $request)
    {
        $rentalMachine = RentalMachine::create($request->validated());

        if (!is_null($request['base64'])) {
            $relativePath  = $this->saveImage($request['base64'], $rentalMachine->default_path_folder);
            $request['base64'] = $relativePath;
            $updateData = ['picture' => $relativePath];
            $rentalMachine->update($updateData);
        }

        return $this->respondCreated($rentalMachine);
    }

    /**
     * Display the specified resource.
     */
    public function show(RentalMachine $rentalMachine)
    {
        return $this->respond($rentalMachine);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRentalMachineRequest $request, RentalMachine $rentalMachine)
    {
        $rentalMachine->update($request->validated());

        if (!is_null($request['base64'])) {
            if ($rentalMachine->picture) {
                Storage::disk('s3')->delete($rentalMachine->picture);
            }
            $relativePath  = $this->saveImage($request['base64'], $rentalMachine->default_path_folder);
            $request['base64'] = $relativePath;
            $updateData = ['picture' => $relativePath];
            $rentalMachine->update($updateData);
        }

        return $this->respond($rentalMachine);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RentalMachine $rentalMachine)
    {
        $rentalMachine->delete();
        return $this->respondSuccess();
    }

    public function getAll()
    {

        $data = [
            'rentalMachinesOn' => RentalMachine::get(),
            'rentalMachines' => RentalMachine::withTrashed()->get(),
            'clientes' => Cliente::get(),
            'empleados' => Empleado::with('sucursal')->get(),
        ];

        return $this->respond($data);
    }
}
