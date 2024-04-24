<?php

namespace App\Http\Controllers\Ecommerce;

use App\Contracts\FeatureContract;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Ecommerce\StoreFeaturesRequest;
use App\Http\Requests\Ecommerce\UpdateFeaturesRequest;
use App\Models\Ecommerce\Features;

class FeaturesController extends ApiController
{

    private FeatureContract $featureRepository;

    public function __construct(FeatureContract $featureRepository)
    {
        $this->featureRepository = $featureRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = $this->featureRepository->index(request()->all());

        return $this->respond([
            'data' => $items,
            'message' => 'Recursos Encontrados'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFeaturesRequest $request)
    {
        $payload = $request->validated();

        $feature = $this->featureRepository->createFeature($payload);

        return $this->respondCreated([
            'success' => true,
            'message' => 'Categoria Creada',
            'data' => $feature
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Features $feature)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFeaturesRequest $request, Features $feature)
    {
        $payload = $request->validated();

        $updated = $this->featureRepository->updateFeature($feature->id, $payload);

        return $this->respondCreated([
            'success' => true,
            'message' => 'Caracteristica Actualizada',
            'data' => $updated
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Features $feature)
    {
        $feature->delete();
        return $this->respondSuccess();
    }
}
