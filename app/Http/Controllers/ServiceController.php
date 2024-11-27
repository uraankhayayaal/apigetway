<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Lumen\Routing\Controller;

class ServiceController extends Controller
{
    public function get(Request $request, string $srviceName, string $servicePath): JsonResponse
    {
        $services = json_decode(
            file_get_contents('../services.json'),
        );

        $serviceHost = null;

        foreach ($services as $service) {
            if ($service->name === $srviceName) {
                $serviceHost = $service->host;
                break;
            }
        }

        $response = Http::withHeaders([
            'host' => 'localhost',
        ])->get(
            "$serviceHost/$servicePath",
            $request->query->all(),
        );

        return response()->json(
            $response->json(),
        );
    }
}
