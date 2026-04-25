<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Products\TractorContrapesoRequest;
use App\Models\Intranet\Contrapesos;
use App\Models\Intranet\Currency;
use App\Models\Intranet\Product;
use Illuminate\Support\Facades\DB;

class TractorContrapesosController extends ApiController
{
    public function index(Request $request)
    {
        $filters = $request->all();

        $contra_pesos = Contrapesos::with([
            'tractorContrapesos',
            'currency'])
            ->filter($filters)
            ->paginate(10);

        return $this->respond(
            $contra_pesos,
            'Lista de contrapesos cargada correctamente'
        );
    }

    public function store(TractorContrapesoRequest $request)
    {
        DB::beginTransaction();

        try {
            $contrapeso = Contrapesos::create($request->validated());

            if ($request->filled('tractores')) {
                $contrapeso->tractorContrapesos()->sync(
                    collect($request->tractores)->pluck('id')
                );
            }

            DB::commit();

            return $this->respondCreated(
                $contrapeso->load('tractorContrapesos'),
                'Contrapeso creado'
            );
        } catch (\Throwable $e) {
            DB::rollBack();
        }
    }

    public function update(
        TractorContrapesoRequest $request,
        Contrapesos $tractor_contrapeso
    ) {
        DB::beginTransaction();

        try {
            $data = $request->safe()->except('tractores');

            $tractor_contrapeso->update($data);

            $ids = collect($request->tractores ?? [])
                ->pluck('id')
                ->toArray();

            $tractor_contrapeso->tractorContrapesos()->sync($ids);

            DB::commit();

            return $this->respond(
                $tractor_contrapeso->load('tractorContrapesos'),
                'Contrapeso actualizado'
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Contrapesos $tractor_contrapeso)
    {
        DB::beginTransaction();
        try {
            $tractor_contrapeso->tractorContrapesos()->detach();
            $tractor_contrapeso->delete();
            DB::commit();
            return $this->respondSuccess(
                'Contrapeso eliminado correctamente'
            );
        } catch (\Throwable $e) {
            DB::rollBack();
        }
    }

    public function getTractores()
    {
        $data = [
            'tractores' => Product::with('category')
            ->whereHas('category', function ($query) {
                $query->where('name', 'Tractores');
            })->get(),
            'monedas' => Currency::all(),
        ];
        return $this->respond($data, 'Lista de tractores cargada correctamente');
    }
}
