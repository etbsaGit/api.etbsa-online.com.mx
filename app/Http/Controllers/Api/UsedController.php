<?php

namespace App\Http\Controllers\Api;

use App\Models\Used;
use App\Models\Linea;
use App\Models\UsedDoc;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use App\Traits\UploadableFile;
use App\Models\Intranet\TipoEquipo;
use App\Http\Requests\Used\PutRequest;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Used\StoreRequest;

class UsedController extends ApiController
{
    use UploadableFile;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        return $this->respond(Used::filter($filters)->with('origin','location','tipoEquipo','linea','usedDoc')->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $used = Used::create($request->validated());
        $docs = $request->archivos;

        if (!empty($docs)) {
            foreach ($docs as $doc) {
                $ud = UsedDoc::create(['used_id' => $used->id, 'name' => $doc['name'], 'extension' => $doc['extension']]);
                $relativePath  = $this->saveDoc($doc['base64'], $ud->default_path_folder);
                $updateData = ['path' => $relativePath];
                $ud->update($updateData);
            }
        }

        return $this->respondCreated($used);
    }

    /**
     * Display the specified resource.
     */
    public function show(Used $used)
    {
        return $this->respond($used);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutRequest $request, Used $used)
    {
        $used->update($request->validated());
        $docs = $request->archivos;

        if (!empty($docs)) {
            foreach ($docs as $doc) {
                $ud = UsedDoc::create(['used_id' => $used->id, 'name' => $doc['name'], 'extension' => $doc['extension']]);
                $relativePath  = $this->saveDoc($doc['base64'], $ud->default_path_folder);
                $updateData = ['path' => $relativePath];
                $ud->update($updateData);
            }
        }

        return $this->respond($used);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Used $used)
    {
        $used->delete();
        return $this->respondSuccess();
    }

    public function getforms()
    {
        $data = [
            'sucursales' => Sucursal::all(),
            'tipos_equipo' => TipoEquipo::all(),
            'lineas' => Linea::all()
        ];
        return $this->respond($data);
    }
}
