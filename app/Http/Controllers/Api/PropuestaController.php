<?php

namespace App\Http\Controllers\Api;

use App\Models\Linea;
use App\Models\Estatus;
use App\Models\Propuesta;
use App\Models\Departamento;
use Illuminate\Http\Request;
use App\Traits\UploadableFile;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Propuesta\StoreRequest;
use App\Models\Ecommerce\Category;

class PropuestaController extends ApiController
{
    use UploadableFile;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        $query = Propuesta::filter($filters)
            ->with(['estatus', 'linea', 'departamento', 'creador.empleado', 'auth', 'category'])
            ->latest();

        // Validar si el usuario tiene el rol "authProp"
        if (!auth()->user()->hasRole('authProp')) {
            $query->where('created_by', auth()->id());
        }

        $propuestas = $query->paginate(10);

        return $this->respond($propuestas);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        // Si "status" viene y no es null, agregar campos de autenticación
        if ($request->has('status') && !is_null($request->input('status'))) {
            $data['auth_by'] = auth()->id();
            $data['auth_at'] = now();
        }

        $propuesta = Propuesta::create($data);

        // Manejo de imagen base64
        if (!is_null($request->input('base64'))) {
            if ($propuesta->image) {
                Storage::disk('s3')->delete($propuesta->image);
            }

            $relativePath = $this->saveImage($request->input('base64'), $propuesta->default_path_folder);

            $propuesta->update(['image' => $relativePath]);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Propuesta $propuestum)
    {
        return $this->respond($propuestum->load('estatus', 'linea', 'departamento', 'creador', 'auth', 'category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, Propuesta $propuestum)
    {
        $data = $request->validated();

        // Validar si el campo "status" viene, no es null, y cambió respecto al valor actual
        if ($request->has('status') && !is_null($request->input('status')) && $request->input('status') !== $propuestum->status) {
            $data['auth_by'] = auth()->id();  // ID del usuario autenticado
            $data['auth_at'] = now();         // Timestamp actual
        }

        $propuestum->update($data);

        // Manejo de imagen base64
        if (!is_null($request->input('base64'))) {
            if ($propuestum->image) {
                Storage::disk('s3')->delete($propuestum->image);
            }

            $relativePath = $this->saveImage($request->input('base64'), $propuestum->default_path_folder);

            $propuestum->update(['image' => $relativePath]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Propuesta $propuestum)
    {
        if ($propuestum->image) {
            Storage::disk('s3')->delete($propuestum->image);
        }
        $propuestum->delete();
        return $this->respondSuccess();
    }

    public function getforms()
    {
        $data = [
            'departamentos' => Departamento::all(),
            'lineas' => Linea::all(),
            'categories' => Category::all(),
            'estatus' => Estatus::where('tipo_estatus', 'propuestaTypes')->get(),
        ];
        return $this->respond($data);
    }
}
