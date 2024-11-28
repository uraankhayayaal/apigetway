<?php

namespace App\Enums;

enum WithBodyRequestMethodEnum: string
{
    case POST = 'post';
    case PUT = 'put';
    case PATCH = 'patch';
    case DELETE = 'delete';
}
