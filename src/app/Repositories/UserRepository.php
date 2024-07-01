<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserRepository extends BaseApiRepository
{
    protected function getBuilder() : Builder
    {
        return User::query();
    }
}