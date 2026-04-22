<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Models\Intranet\Egreso;
use App\Models\Intranet\Cliente;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Egreso\StoreRequest;
use App\Models\Intranet\ExchangeRate;

class ExchangeRateController extends ApiController
{
    public function index()
    {
        $tarifa = ExchangeRate::latest()->first();
        return $this->respond($tarifa);
    }

    public function store(Request $request)
    {
        $request->validate([
            'value' => 'required|numeric',
        ]);
        $tarifa = ExchangeRate::create([
            'value' => $request->value,
        ]);
        return $this->respondCreated($tarifa);
    }
}
