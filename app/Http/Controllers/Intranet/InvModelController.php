<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Traits\UploadableFile;
use App\Models\Intranet\InvModel;
use App\Http\Controllers\Controller;
use App\Models\Intranet\InvCategory;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Intranet\InvModel\StoreRequest;
use App\Models\Intranet\InvConfiguration;

class InvModelController extends ApiController
{
    use UploadableFile;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $invModels = InvModel::filter($filters)->with('invConfigurations.invCategory')->paginate(10);
        return $this->respond($invModels);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        $invConfigurations = $data['inv_configurations'] ?? [];
        unset($data['inv_configurations']);

        $invModel = InvModel::create($data);

        $invModel->invConfigurations()->sync($invConfigurations);

        if (!empty($request['base64'])) {
            $relativePath = $this->saveDoc($request['base64'], $invModel->default_path_folder);
            $invModel->update(['path' => $relativePath]);
        }

        return $this->respondCreated($invModel->load('invConfigurations'));
    }


    /**
     * Display the specified resource.
     */
    public function show(InvModel $invModel)
    {
        return $this->respond($invModel);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, InvModel $invModel)
    {
        $data = $request->validated();

        // Extraer configuraciones antes del update
        $invConfigurations = $data['inv_configurations'] ?? [];
        unset($data['inv_configurations']);

        // Update del modelo
        $invModel->update($data);

        // Sync de configuraciones (admite array vacío)
        $invModel->invConfigurations()->sync($invConfigurations);

        // Manejo del archivo
        if (!is_null($request['base64'])) {
            if ($invModel->path) {
                Storage::disk('s3')->delete($invModel->path);
            }

            $relativePath = $this->saveDoc(
                $request['base64'],
                $invModel->default_path_folder
            );

            $invModel->update(['path' => $relativePath]);
        }

        return $this->respond(
            $invModel->load('invConfigurations')
        );
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvModel $invModel)
    {
        $invModel->delete();
        return $this->respondSuccess();
    }

    public function getForms()
    {
        $data = [
            'invCategories' => InvCategory::with('invGroup', 'status')
                ->orderBy('name')
                ->get(),
        ];

        return $this->respond($data);
    }


    public function perCategory(InvCategory $invCategory)
    {
        $invCategory->load([
            'invConfigurations' => function ($query) {
                $query->orderBy('name');
            },
            'invConfigurations.invCategory',
        ]);

        $data = [
            'invConfigurations' => $invCategory->invConfigurations,
        ];

        return $this->respond($data);
    }
}
