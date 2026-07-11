<?php

namespace App\Http\Controllers\Intranet\PowerBI;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;


class DwhHistoricalController extends ApiController
{
    public function clientes()
    {
        return response()->json(DB::table('historical_dim_cliente')->get());
    }

    public function contacto()
    {
        return response()->json(DB::table('historical_bridge_cliente_contacto')->get());
    }

    public function cultivos()
    {
        return response()->json(DB::table('historical_dim_cultivoscliente')->get());
    }

    public function distribucion()
    {
        return response()->json(DB::table('historical_dim_distribucioncliente')->get());
    }

    public function equipo()
    {
        return response()->json(DB::table('historical_dim_equipocliente')->get());
    }

    public function fincas()
    {
        return response()->json(DB::table('historical_dim_fincascliente')->get());
    }

    public function riego()
    {
        return response()->json(DB::table('historical_dim_riegoclientes')->get());
    }

    public function tecnologias()
    {
        return response()->json(DB::table('historical_dim_tecnologiascliente')->get());
    }
}
