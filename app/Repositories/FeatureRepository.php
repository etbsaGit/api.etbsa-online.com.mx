<?php

namespace App\Repositories;

use App\Contracts\FeatureContract;
use App\Models\Ecommerce\Features;
use App\Traits\UploadableFile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class FeatureRepository extends BaseRepository implements FeatureContract
{

    use UploadableFile;

    public function __construct(Features $feature)
    {
        parent::__construct($feature);
        $this->model = $feature; // This is the model that we are binding to
    }

    public function index(
        array $params = [
            'paginate' => 'no'
        ]
    ) {
        return $this->get(
            $params,
            [],
            function ($query) use ($params) {
                // local scopes
                return $query;
            }
        );

    }

    // public function filterFor(array $params)
    // {
    //     // TODO: Implement filterFor() method.
    // }

    public function listFeature(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        return $this->all($columns, $order, $sort);
    }

    public function findFeatureById(int $id)
    {
        try {
            return $this->findOneOrFail($id);

        } catch (ModelNotFoundException $e) {

            throw new ModelNotFoundException($e);
        }
    }

    public function createFeature(array $params)
    {
        try {
            $collection = collect($params);
            $feature = new Features($collection->all());
            $feature->save();
            return $feature;

        } catch (QueryException $exception) {
            throw new \InvalidArgumentException($exception->getMessage());
        } catch (\Exception $e) {
            \Log::debug($e->getMessage());
        }
    }

    public function updateFeature(Features $feature, array $params)
    {
        // $feature = $this->findOneOrFail($id);

        $collection = collect($params)->except('_token');

        $feature->update($collection->all());

        return $feature;
    }

    public function deleteFeature($id)
    {
        $feature = $this->findFeatureById($id);

        $feature->delete();

        return $feature;
    }
}
