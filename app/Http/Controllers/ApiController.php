<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller;

class ApiController extends Controller
{
    public function get(): JsonResponse
    {
        return response()->json(
            json_decode(
                file_get_contents('../openapi.json'),
            ),
        );
    }
}
