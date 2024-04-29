<?php

namespace App\Repositories;

use App\Contracts\BaseContract;
use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class BaseRepository
 *
 * @package \App\Repositories
 */
class BaseRepository implements BaseContract
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $params
     * @param array $with
     * @param callable|null $callable
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function get(array $params = [], array $with = [], $callable = null)
    {
        $q = $this->model->with($with);
        if (!is_null($callable)) {
            $q = call_user_func_array($callable, [&$q]);
        }

        if (Helpers::hasValue($params['per_page']) && ($params['per_page'] == -1)) {
            $params['per_page'] = 999999999999;
        }
        if (Helpers::hasValue($params['paginate']) && ($params['paginate'] == 'no')) {
            return $q->get($params['columns'] ?? '*');
        }
        $q->orderBy($params['order_by'] ?? 'id', $params['order_sort'] ?? 'desc');

        return $q->paginate($params['per_page'] ?? 10, $params['columns'] ?? '*');
    }

    /**
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    /**
     * @param array $attributes
     * @param int $id
     * @return bool
     */
    public function update(array $attributes, int $id): bool
    {
        return $this->find($id)->update($attributes);
    }

    /**
     * @param array $columns
     * @param string $orderBy
     * @param string $sortBy
     * @return mixed
     */
    public function all($columns = array('*'), string $orderBy = 'id', string $sortBy = 'asc')
    {
        return $this->model->orderBy($orderBy, $sortBy)->get($columns);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        return $this->model->find($id);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws ModelNotFoundException
     */
    public function findOneOrFail(int $id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function findBy(array $data)
    {
        return $this->model->where($data)->all();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function findOneBy(array $data)
    {
        return $this->model->where($data)->first();
    }

    /**
     * @param array $data
     * @return mixed
     * @throws ModelNotFoundException
     */
    public function findOneByOrFail(array $data)
    {
        return $this->model->where($data)->firstOrFail();
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->model->find($id)->delete();
    }

    public function filter(array $params)
    {
        return $this->model->filter($params);
    }

    public function orderByName()
    {
        return $this->model->orderByName();
    }
}
