<?php

namespace App\Repositories;

use App\Contracts\BrandContract;
use App\Models\Ecommerce\Brand;
use App\Traits\UploadableFile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

class BrandRepository extends BaseRepository implements BrandContract
{

    use UploadableFile;

    public function __construct(Brand $brand)
    {
        parent::__construct($brand);
        $this->brand = $brand;
    }

    public function index(
        array $params = []
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

    public function listBrands(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        return $this->all($columns, $order, $sort);
    }

    public function findBrandById(int $id)
    {
        try {
            return $this->findOneOrFail($id);

        } catch (ModelNotFoundException $e) {

            throw new ModelNotFoundException($e);
        }
    }

    public function createBrand(array $params)
    {
        try {
            $collection = collect($params);

            $logo = null;

            if ($collection->has('logo') && ($collection->get('logo') instanceof UploadedFile)) {
                $logo = $this->uploadOne($params['logo'], 'images/brands', 's3');
            }

            $merge = $collection->merge(compact('logo'));

            $brand = new Brand($merge->all());
            $brand->save();

            return $brand;

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        } catch (\Exception $e) {
            \Log::debug($e->getMessage());
        }
    }

    public function updateBrand(Brand $brand, array $params)
    {
        // $brand = $this->findBrandById($id);
        $collection = collect($params)->except('_token');
        $slug = \Str::slug($collection->get('name'));
        $logo = null;

        if ($collection->has('logo') && ($collection->get('logo') instanceof UploadedFile)) {
            if (!is_null($brand->logo)) {
                $this->deleteLogo($brand);
            }
            $logo = $this->uploadOne($params['logo'], 'images/brands', 's3');
        }

        if (!is_null($logo)) {
            $merge = $collection->merge(compact('logo', 'slug'));
        }

        if (is_null($logo)) {
            $merge = $collection->merge(compact('slug'));
        }

        $brand->update($merge->all());

        return $brand;
    }

    public function deleteBrand($id)
    {
        $brand = $this->findBrandById($id);

        $brand->delete();

        return $brand;
    }

    public function deleteLogo(Brand $brand): bool
    {

        if (\Storage::disk('s3')->delete($brand->storageLogo)) {
            $brand->logo = null;
            $brand->save();

            return true;
        }

        return false;
    }
}
