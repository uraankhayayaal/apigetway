<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Routing\Controller as BaseController;

class ApiController extends BaseController
{
    public function get(Request $request): array
    {
        $user = Auth::user();

        $user = $request->user();

        return [
            $user,
        ];
    }
}
