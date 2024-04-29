<?php

namespace App\Repositories;

use App\Contracts\ProductContract;
use App\Models\Ecommerce\Product;
use App\Traits\UploadableFile;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;

class ProductRepository extends BaseRepository implements ProductContract
{

    use UploadableFile;

    public function __construct(Product $product)
    {
        parent::__construct($product);
        $this->model = $product; // This is the model that we are binding to

    }

    public function index(
        array $params = []
    ) {
        return $this->get(
            $params,
            ['images', 'categories'],
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

    public function listProducts(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        return $this->all($columns, $order, $sort);
    }

    public function findProductById(int $id)
    {
        try {
            return $this->findOneOrFail($id);

        } catch (ModelNotFoundException $e) {

            throw new ModelNotFoundException($e);
        }
    }

    public function createProduct(array $params)
    {
        return DB::transaction(function () use ($params) {
            // try {
            $collection = collect($params);
            $product = new Product($collection->all());
            $product->save();

            if ($collection->has('category_id')) {
                $product->categories()->sync($collection['category_id']); // [1,2,3]
            }
            if ($collection->has('features')) {
                $product->categories()->sync($collection['features']->map(function ($feature, $key) {
                    return [$key => ['value' => $feature->value]];
                }));
                // [ 1 => ['value' => "valor"]]
            }

            if ($images = $collection->get('images') ?? []) {
                foreach ($images as $image) {
                    if ($image instanceof UploadedFile) {
                        $product->images()->create([
                            'path' => $this->uploadOne(
                                $image,
                                'images/products/' . $product->id . '/',
                                's3',
                                $image->getClientOriginalName()
                            )
                        ]);
                    }
                }
            }

            return $product;

            // } catch (QueryException $exception) {
            //     throw new \InvalidArgumentException($exception->getMessage());
            // }
        });
    }

    public function updateProduct(int $id, array $params)
    {
        $collection = collect($params);

        $product = $this->findProductById($params['product_id']);
        $product->update($params);

        if ($collection->has('category_id')) {
            $product->categories()->sync($params['category_id']);
        }

        if ($collection->has('features')) {
            $product->categories()->sync($collection['features']->map(function ($feature, $key) {
                return [$key => ['value' => $feature->value]];
            }));
            // [ 1 => ['value' => "valor"]]
        }

        return $product;
    }

    public function deleteProduct($id)
    {
        $product = $this->findProductById($id);

        $product->delete();

        return $product;
    }
}
