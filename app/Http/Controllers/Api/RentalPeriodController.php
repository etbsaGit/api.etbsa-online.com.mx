<?php

namespace App\Http\Controllers\Api;

use App\Models\Puesto;
use App\Models\Empleado;
use App\Models\Departamento;
use App\Models\RentalPeriod;
use Illuminate\Http\Request;
use App\Traits\UploadableFile;
use App\Mail\RentPeriodMailable;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\RentalPeriod\PutRentalPeriodRequest;
use App\Http\Requests\RentalPeriod\StoreRentalPeriodRequest;

class RentalPeriodController extends ApiController
{
    use UploadableFile;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        $rentalPeriods = RentalPeriod::filter($filters)
            ->with('cliente', 'rentalMachine')
            ->orderBy('end_date')
            ->paginate(10);

        return $this->respond($rentalPeriods);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRentalPeriodRequest $request)
    {
        $rentalPeriod = RentalPeriod::create($request->validated());

        if (!is_null($request['base64'])) {
            $relativePath  = $this->saveDoc($request['base64'], $rentalPeriod->default_path_folder);
            $request['base64'] = $relativePath;
            $updateData = ['document' => $relativePath];
            $rentalPeriod->update($updateData);
        }

        return $this->respondCreated($rentalPeriod);
    }

    /**
     * Display the specified resource.
     */
    public function show(RentalPeriod $rentalPeriod)
    {
        return $this->respond($rentalPeriod);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRentalPeriodRequest $request, RentalPeriod $rentalPeriod)
    {
        $rentalPeriod->update($request->validated());

        if (!is_null($request['base64'])) {
            if ($rentalPeriod->document) {
                Storage::disk('s3')->delete($rentalPeriod->document);
            }
            $relativePath  = $this->saveDoc($request['base64'], $rentalPeriod->default_path_folder);
            $request['base64'] = $relativePath;
            $updateData = ['document' => $relativePath];
            $rentalPeriod->update($updateData);
        }

        return $this->respond($rentalPeriod);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RentalPeriod $rentalPeriod)
    {
        $rentalPeriod->delete();
        return $this->respondSuccess();
    }

    public function getPerCalendar()
    {
        $today = now(); // Fecha y hora actuales

        $activePeriods = RentalPeriod::where('end_date', '>', $today)->with('cliente', 'rentalMachine')->get();

        return $this->respond($activePeriods);
    }

    public function sendNotify(RentalPeriod $rentalPeriod)
    {
        $contadorGeneral = Empleado::where('puesto_id', Puesto::where('nombre', 'Contador general')->value('id'))->first();

        $gerenteRentas = Empleado::where('puesto_id', Puesto::where('nombre', 'Gerente')->value('id'))
            ->where('departamento_id', Departamento::where('nombre', 'Rentas')->value('id'))
            ->first();

        $vendedorAsignado = $rentalPeriod->empleado;

        $correos = [
            'contadorGeneral' => $contadorGeneral->user?->email,
            'gerenteRentas' => $gerenteRentas->user?->email,
            'vendedorAsignado' => $vendedorAsignado->user?->email,
        ];

        foreach ($correos as $to_email) {
            if ($to_email) {
                Mail::to($to_email)->send(new RentPeriodMailable($rentalPeriod->load('empleado','cliente.town','cliente.stateEntity','rentalMachine')));
            }
        }

        // Retorna la respuesta junto con el empleado encontrado
        return $this->respondSuccess();
    }
}
