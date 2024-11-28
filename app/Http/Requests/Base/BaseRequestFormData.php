<?php

declare(strict_types=1);

namespace App\Http\Requests\Base;

use Illuminate\Http\Request;
use ReflectionProperty;

abstract class BaseRequestFormData extends BaseRequestData
{
    protected function getPropValue(Request $request, ReflectionProperty $prop): mixed
    {
        return $request->input($prop->getName()); // return types: string, bool, int, float, etc
    }
}
