<?php

namespace App\Repositories;

use App\Contracts\VendorContract;
use App\Models\Ecommerce\Vendor;
use App\Traits\UploadableFile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

class VendorRepository extends BaseRepository implements VendorContract
{

    use UploadableFile;

    public function __construct(Vendor $vendor)
    {
        parent::__construct($vendor);
        $this->vendor = $vendor;
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

    public function filterFor(array $params)
    {
        // TODO: Implement filterFor() method.
    }

    public function listVendors(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        return $this->all($columns, $order, $sort);
    }

    public function findVendorById(int $id)
    {
        try {
            return $this->findOneOrFail($id);

        } catch (ModelNotFoundException $e) {

            throw new ModelNotFoundException($e);
        }
    }

    public function createVendor(array $params)
    {
        try {
            $collection = collect($params);

            $logo = null;

            if ($collection->has('logo') && ($collection->get('logo') instanceof UploadedFile)) {
                $logo = $this->uploadOne($params['logo'], 'images/vendors', 's3');
            }

            $merge = $collection->merge(compact('logo'));

            $vendor = new Vendor($merge->all());

            $vendor->save();

            return $vendor;

        } catch (QueryException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        } catch (\Exception $e) {
            \Log::debug($e->getMessage());
        }
    }

    public function updateVendor(int $id, array $params)
    {
        $vendor = $this->findVendorById($id);

        $collection = collect($params)->except('_token');
        $slug = \Str::slug($collection->get('name'));
        $logo = null;

        if ($collection->has('logo') && ($collection->get('logo') instanceof UploadedFile)) {
            if (!is_null($vendor->logo)) {
                $this->deleteLogo($vendor);
            }
            $logo = $this->uploadOne($params['logo'], 'images/vendors', 's3');
        }

        if (!is_null($logo)) {
            $merge = $collection->merge(compact('logo', 'slug'));
        }

        if (is_null($logo)) {
            $merge = $collection->merge(compact('slug'));
        }

        $vendor->update($merge->all());

        return $vendor;
    }

    public function deleteVendor($id)
    {
        $vendor = $this->findVendorById($id);
        $this->deleteLogo($vendor);
        $vendor->delete();

        return $vendor;
    }

    public function deleteLogo(Vendor $vendor): bool
    {

        if (\Storage::disk('s3')->delete($vendor->storageLogo)) {
            $vendor->logo = null;
            $vendor->save();

            return true;
        }

        return false;
    }
}
