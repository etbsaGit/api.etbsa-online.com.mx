<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Traits\UploadableFile;
use App\Models\Intranet\InvItem;
use App\Models\Intranet\InvModel;
use App\Models\Intranet\InvFactory;
use App\Models\Intranet\InvItemDoc;
use App\Models\Intranet\TipoEquipo;
use App\Models\Intranet\InvCategory;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\InvItem\InvItemRequest;
use App\Models\Sucursal;

class InvItemController extends ApiController
{
    use UploadableFile;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $invItems = InvItem::filter($filters)->with('invModel', 'invConfigurations', 'invItemDocs')->paginate(10);
        return $this->respond(
            $invItems,
            'Inventario cargado correctamente'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InvItemRequest $request)
    {
        $data = $request->validated();

        $invConfigurations = $data['inv_configurations'] ?? [];
        unset($data['inv_configurations']);

        $invItem = InvItem::create($data);

        $invItem->invConfigurations()->sync($invConfigurations);

        $docs = $request->base64;

        if (!empty($docs)) {
            foreach ($docs as $doc) {
                $iid = InvItemDoc::create([
                    "name" => $doc['name'],
                    "inv_item_id" => $invItem->id
                ]);
                $relativePath  = $this->saveDoc($doc['base64'], $iid->default_path_folder);
                $iid->update(['path' => $relativePath]);
            }
        }

        return $this->respondCreated(
            $invItem->load('invConfigurations'),
            'Item registrado correctamente'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(InvItem $invItem)
    {
        return $this->respond(
            $invItem,
            'Detalle del Item'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InvItemRequest $request, InvItem $invItem)
    {
        $data = $request->validated();

        $invConfigurations = $data['inv_configurations'] ?? [];
        unset($data['inv_configurations']);

        $invItem->update($data);

        $invItem->invConfigurations()->sync($invConfigurations);

        $docs = $request->base64;

        if (!empty($docs)) {
            foreach ($docs as $doc) {
                $iid = InvItemDoc::create([
                    "name" => $doc['name'],
                    "inv_item_id" => $invItem->id
                ]);
                $relativePath  = $this->saveDoc($doc['base64'], $iid->default_path_folder);
                $iid->update(['path' => $relativePath]);
            }
        }

        return $this->respond(
            $invItem->load('invConfigurations'),
            'Item actualizado correctamente'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvItem $invItem)
    {
        $invItem->delete();
        return $this->respondSuccess(
            'Item eliminado correctamente'
        );
    }

    public function getForms()
    {
        $invModels = InvModel::all();
        $invFactories = InvFactory::all();
        $sucursales = Sucursal::all();

        return $this->respond([
            'sucursales' => $sucursales,
            'invModels' => $invModels,
            'invFactories' => $invFactories,
        ]);
    }

    public function getModels(InvModel $invModel)
    {
        $invCategories = InvCategory::query()
            ->with([
                'invConfigurations' => function ($q) use ($invModel) {
                    $q->whereRelation('invModels', 'inv_models.id', $invModel->id)
                        ->orderBy('code', 'asc'); // (1) configs ordenadas
                }
            ])
            ->whereHas('invConfigurations.invModels', function ($q) use ($invModel) {
                $q->where('inv_models.id', $invModel->id);
            })
            ->withMin(
                ['invConfigurations as min_code' => function ($q) use ($invModel) {
                    $q->whereRelation('invModels', 'inv_models.id', $invModel->id);
                }],
                'code'
            ) // (2) calcula el mínimo code por categoría, filtrado por modelo
            ->orderBy('min_code', 'asc') // (3) ordena categorías por ese mínimo
            ->get();

        return $this->respond([
            'invCategory' => $invCategories,
        ]);
    }
}
