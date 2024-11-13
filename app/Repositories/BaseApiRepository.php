<?php

namespace App\Repositories;

use App\Repositories\Interfaces\ApiRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class BaseApiRepository implements ApiRepositoryInterface
{
    abstract protected function getBuilder() : Builder;

    public function getAll() : LengthAwarePaginator
    {
        return $this->getBuilder()->paginate(20);
    }

    public function delete(Model $model) : int
    {
        return $model->delete();
    }

    public function create(array $params) : Model
    {
        $model = $this->getBuilder()->getModel()->create($params);

        return $model;
    }

    public function update(Model $model, array $params) : Model
    {
        $model->update($params);

        return $model;
    }
}