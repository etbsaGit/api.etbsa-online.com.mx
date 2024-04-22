<?php

namespace App\Repositories;

use App\Contracts\CategoryContract;
use App\Models\Ecommerce\Category;
use App\Traits\UploadableFile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class CategoryRepository extends BaseRepository implements CategoryContract
{

    use UploadableFile;

    public function __construct(Category $category)
    {
        parent::__construct($category);
        $this->model = $category; // This is the model that we are binding to

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

    public function filterFor(array $params)
    {
        // TODO: Implement filterFor() method.
    }

    public function listCategories(string $order = 'id', string $sort = 'desc', array $columns = ['*'])
    {
        return $this->all($columns, $order, $sort);
    }

    public function findCategoryById(int $id)
    {
        try {
            return $this->findOneOrFail($id);

        } catch (ModelNotFoundException $e) {

            throw new ModelNotFoundException($e);
        }
    }

    public function createCategory(array $params)
    {
        try {
            $collection = collect($params);
            $category = new Category($collection->all());
            $category->save();
            return $category;

        } catch (QueryException $exception) {
            throw new \InvalidArgumentException($exception->getMessage());
        } catch (\Exception $e) {
            \Log::debug($e->getMessage());
        }
    }

    public function updateCategory(int $id, array $params)
    {
        $category = $this->findOneOrFail($id);

        $collection = collect($params)->except('_token');
        $slug = \Str::slug($collection->get('name'));

        $merge = $collection->merge(compact('slug'));

        $category->update($merge->all());

        return $category;
    }

    public function deleteCategory($id)
    {
        $category = $this->findCategoryById($id);

        $category->delete();

        return $category;
    }
}
