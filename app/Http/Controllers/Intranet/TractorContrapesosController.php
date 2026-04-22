<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Products\TractorContrapesoRequest;
use App\Models\Intranet\Contrapesos;
use App\Models\Intranet\Product;
use Illuminate\Support\Facades\DB;

class TractorContrapesosController extends ApiController
{
    public function index(Request $request)
    {
        $filters = $request->all();

        $contra_pesos = Contrapesos::with(['tractorContrapesos'])
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

    public function update(TractorContrapesoRequest $request, Contrapesos $tractor_contrapesos)
    {
        $tractor_contrapesos->update($request->validated());

        if ($request->has('tractores')) {
            $tractor_contrapesos->tractorContrapesos()->sync(collect($request->tractores)->pluck('id'));
        }

        return $this->respond(
            $tractor_contrapesos->load('tractorContrapesos'),
            'Contrapeso actualizado'
        );
    }

    public function destroy(Contrapesos $tractor_contrapesos)
    {
        $tractor_contrapesos->delete();
        return $this->respondSuccess(
            'Contrapeso eliminado correctamente'
        );
    }

    public function getTractores()
    {
        $tractores = Product::with('category')
            ->whereHas('category', function ($query) {
                $query->where('name', 'Tractores');
            })->get();
        return $this->respond($tractores, 'Lista de tractores cargada correctamente');
    }
}
