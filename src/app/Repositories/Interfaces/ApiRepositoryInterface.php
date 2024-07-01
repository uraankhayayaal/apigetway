<?php

namespace App\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface ApiRepositoryInterface
{
    public function getAll() : LengthAwarePaginator;

    public function create(array $params) : Model;

    public function update(Model $model, array $params) : Model;

    public function delete(Model $model) : int;
}