<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\WithBodyRequestMethodEnum;
use App\Enums\WithoutBodyRequestMethodEnum;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ServiceController extends Controller
{
    /** @var array<string,stdClass> */
    private array $services;

    public function __construct()
    {
        $services = json_decode(
            file_get_contents('../services.json'),
        );

        foreach ($services as $service) {
            $this->services[$service->name] = $service;
        }
    }

    public function get(Request $request, string $serviceName, string $servicePath): JsonResponse
    {
        $response = $this->handleRequestWithoutBody(WithoutBodyRequestMethodEnum::GET, $request, $serviceName, $servicePath);

        return response()->json(
            $response->json(),
        );
    }

    public function head(Request $request, string $serviceName, string $servicePath): JsonResponse
    {
        $response = $this->handleRequestWithoutBody(WithoutBodyRequestMethodEnum::HEAD, $request, $serviceName, $servicePath);

        return response()->json(
            $response->json(),
        );
    }

    public function post(Request $request, string $serviceName, string $servicePath): JsonResponse
    {
        $response = $this->handleRequestWithBody(WithBodyRequestMethodEnum::POST, $request, $serviceName, $servicePath);

        return response()->json(
            $response->json(),
        );
    }

    public function put(Request $request, string $serviceName, string $servicePath): JsonResponse
    {
        $response = $this->handleRequestWithBody(WithBodyRequestMethodEnum::PUT, $request, $serviceName, $servicePath);

        return response()->json(
            $response->json(),
        );
    }

    public function patch(Request $request, string $serviceName, string $servicePath): JsonResponse
    {
        $response = $this->handleRequestWithBody(WithBodyRequestMethodEnum::PATCH, $request, $serviceName, $servicePath);

        return response()->json(
            $response->json(),
        );
    }

    public function delete(Request $request, string $serviceName, string $servicePath): JsonResponse
    {
        $response = $this->handleRequestWithBody(WithBodyRequestMethodEnum::DELETE, $request, $serviceName, $servicePath);

        return response()->json(
            $response->json(),
        );
    }

    public function options(Request $request, string $serviceName, string $servicePath): Response
    {
        // TODO: update here
        return response('', 200, [
            'Date' => 'Mon, 01 Dec 2008 01:15:39 GMT',
            'Server' => 'Apache/2.0.61 (Unix)',
            'Access-Control-Allow-Origin' => 'https://foo.example',
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'X-PINGOTHER, Content-Type',
            'Access-Control-Max-Age' => '86400',
            'Vary' => 'Accept-Encoding, Origin',
            'Keep-Alive' => 'timeout=2, max=100',
            'Connection' => 'Keep-Alive',
        ]);
    }

    private function handleRequestWithoutBody(WithoutBodyRequestMethodEnum $method, Request $request, string $serviceName, string $servicePath): ClientResponse
    {
        return $this->getClientWithHeaders()
            ->{$method->value}(
                "{$this->getServiceHost($serviceName)}/$servicePath",
                $request->query->all(),
            )
            ->onError(function (ClientResponse $response) use ($serviceName, $servicePath): void {
                $this->catchError($response, $serviceName, $servicePath);
            });
    }

    private function handleRequestWithBody(WithBodyRequestMethodEnum $method, Request $request, string $serviceName, string $servicePath): ClientResponse
    {
        $servicePathWithQuery = $servicePath . $this->getQuery($request);

        return $this->getClientWithHeaders()
            ->{$method->value}(
                "{$this->getServiceHost($serviceName)}/$servicePathWithQuery",
                $request->input(),
            )
            ->onError(function (ClientResponse $response) use ($serviceName, $servicePathWithQuery): void {
                $this->catchError($response, $serviceName, $servicePathWithQuery);
            });
    }

    private function getClientWithHeaders(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'host' => 'localhost',
            'Content-Type' => 'application/json',
        ]);
    }

    private function getServiceHost(string $serviceName): string
    {
        if (isset($this->services[$serviceName])) {
            return $this->services[$serviceName]->host;
        }

        throw new HttpException(404, "Service name not found: $serviceName");
    }

    private function getQuery(Request $request): string
    {
        $params = http_build_query($request->query->all(), '', '&', PHP_QUERY_RFC3986);
        return !empty($params) ? "?$params" : '';
    }

    private function catchError(ClientResponse $response, string $serviceName, string $servicePath): void
    {
        throw new HttpException($response->status(), "Error from `$serviceName` service request by `$servicePath` path: {$response->reason()}. {$response->body()}", $response->toException());
    }
}
