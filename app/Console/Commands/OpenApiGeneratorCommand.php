<?php

namespace App\Console\Commands;

use App\Enums\OpenApiScalarTypesMapEnum;
use App\Http\Requests\Base\BaseRequestFormData;
use App\Http\Requests\Base\BaseRequestQueryData;
use Closure;
use Illuminate\Console\Command;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Route;
use OpenApi\Analysis;
use OpenApi\Attributes as OA;
use OpenApi\Context;
use OpenApi\Serializer;
use ReflectionClass;
use ReflectionMethod;

class OpenApiGeneratorCommand extends Command
{
    private const AUTH_MIDDLEWARE = 'auth';

    private const BASE_FORM_DATA_CLASS = BaseRequestFormData::class;

    private const BASE_QUERY_DATA_CLASS = BaseRequestQueryData::class;

    private const BASE_RESPONSE_CLASS = JsonResource::class;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openapi:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate openapi documentation';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $analysis = new Analysis([], new Context());
        $analysis->openapi = new OA\OpenApi();
        $analysis->openapi->info = new OA\Info(title: 'MY API GETWAY', version: '0.0.1');
        $analysis->openapi->servers = [
            new OA\Server(
                url: 'http://localhost:3010',
                description: 'Local API server'
            ),
            new OA\Server(
                url: 'https://2children.ru',
                description: 'Production API server'
            ),
        ];

        $componentSchemas = [];

        $analysis->openapi->paths = [];
        $analysis->openapi->tags = [];
        foreach (Route::getRoutes() as $route) {
            if ($this->isClosure($route['action']) || !isset($route['action']['as'])) { // skip callbacks from routs
                continue;
            }

            $httpMethod = strtolower($route['method']);
            $httpMethodClass = 'OpenApi\\Attributes\\' . ucfirst($httpMethod);

            $path = new $httpMethodClass(
                security: $this->getSecurity($route['action']),
                tags: [$route['action']['as']],
                parameters: $this->buildParameters($route['action']),
                requestBody: $this->buildRequestBody($route['action']),
                responses: $this->buildResponses($route['action'], $componentSchemas),
            );

            $analysis->openapi->paths[] = new OA\PathItem(
                ...[
                    'path' => $route['uri'],
                    $httpMethod => $path,
                ],
            );
        }

        $analysis->openapi->components = new OA\Components(
            schemas: $componentSchemas,
            securitySchemes: [
                new OA\SecurityScheme(
                    securityScheme: 'bearerAuth',
                    type: 'http',
                    scheme: 'bearer',
                    bearerFormat: 'JWT',
                ),
            ],
        );

        $services = json_decode(
            file_get_contents('./services.json'),
        );

        foreach ($services as $service) {
            $abstractAnnotation = (new Serializer())->deserializeFile($service->openapi, 'json', \OpenApi\Annotations\OpenApi::class);
            $serviceAnalysis = new Analysis([$abstractAnnotation], new Context());
            // $serviceAnalysis->openapi->saveAs("./{$service->name}.json", 'json');

            foreach ($serviceAnalysis->openapi->paths as $servicePath) {
                foreach ($servicePath->operations() as $operation) {
                    $operation->tags = [$service->name];
                }
                $servicePath->path = "/api/{$service->name}{$servicePath->path}";
                $analysis->openapi->paths[] = $servicePath;
            }
            $analysis->openapi->tags[] = new OA\Tag(name: $service->name);

            foreach ($serviceAnalysis->openapi->components->schemas as $serviceComponentsSchema) {
                $analysis->openapi->components->schemas[] = $serviceComponentsSchema;
            }
        }

        $analysis->openapi->saveAs('./openapi.json', 'json');
    }

    /**
     * @param Closure[]|string[] $action
     *
     * @return OA\Parameter[]
     */
    private function buildParameters(array $action): array
    {
        $refMethod = $this->getReflectionMethod($action['uses']);

        $parameters = [];

        foreach ($refMethod->getParameters() as $param) { // this params places at path
            $typeName = (string) $param->getType();

            // Query params
            if (class_exists($typeName)) {
                $reflect = new ReflectionClass($typeName);
                if ($reflect->isSubclassOf(self::BASE_QUERY_DATA_CLASS)) {
                    $props = $reflect->getProperties();
                    foreach ($props as $prop) {
                        $type = OpenApiScalarTypesMapEnum::tryFrom((string) $prop->getType()); // get only defined types for openapi
                        $type && $parameters[] = new OA\Parameter(
                            name: $prop->getName() . ($type === OpenApiScalarTypesMapEnum::ARRAY || $type === OpenApiScalarTypesMapEnum::NULLABLE_ARRAY ? '[]' : ''),
                            in: 'query',
                            required: !$type->isNullable(),
                            example: $prop->isDefault() ? $prop->getDefaultValue() : null,
                            schema: $type->getSwaggerType(),
                        );
                    }
                }
            }

            // Path params
            $type = OpenApiScalarTypesMapEnum::tryFrom($typeName); // get only defined types for openapi
            $type && $parameters[] = new OA\Parameter(
                name: $param->getName() . ($type === OpenApiScalarTypesMapEnum::ARRAY || $type === OpenApiScalarTypesMapEnum::NULLABLE_ARRAY ? '[]' : ''),
                in: 'path',
                required: !$param->isOptional(),
                example: $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
                schema: $type->getSwaggerType(),
            );
        }

        return $parameters;
    }

    /**
     * @param Closure[]|string[] $action
     *
     * @return null|OA\RequestBody
     */
    private function buildRequestBody(array $action): ?OA\RequestBody
    {
        $refMethod = $this->getReflectionMethod($action['uses']);

        $requestObject = [
            'required' => [],
            'properties' => [],
        ];

        foreach ($refMethod->getParameters() as $param) {
            $typeName = (string) $param->getType();
            if (class_exists($typeName)) {
                $reflect = new ReflectionClass($typeName);
                if ($reflect->isSubclassOf(self::BASE_FORM_DATA_CLASS)) {
                    $props = $reflect->getProperties();
                    foreach ($props as $prop) {
                        $type = OpenApiScalarTypesMapEnum::tryFrom((string) $prop->getType()); // get only defined types for openapi
                        if ($type) {
                            !$type->isNullable() && $requestObject['required'][] = $prop->getName();
                            $requestObject['properties'][] = new OA\Property(
                                property: $prop->getName() . ($type === OpenApiScalarTypesMapEnum::ARRAY || $type === OpenApiScalarTypesMapEnum::NULLABLE_ARRAY ? '[]' : ''),
                                type: $type->getPropertyType(),
                                format: $type->getPropertyFormat(),
                                nullable: $type->isNullable(),
                            );
                        }
                    }
                }
            }
        }

        if (empty($requestObject['properties'])) {
            return null;
        }

        return new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    type: 'object',
                    required: $requestObject['required'],
                    properties: $requestObject['properties'],
                ),
            ),
        );
    }

    /**
     * @param array<array-key,mixed> $action
     * @param array<array-key,OA\Schema> $componentSchemas
     *
     * @return array<array-key,OA\Response>
     */
    private function buildResponses(array $action, array &$componentSchemas): array
    {
        if (!isset($action['uses'])) {
            return [];
        }

        $refMethod = $this->getReflectionMethod($action['uses']);

        $typeName = (string) $refMethod->getReturnType();

        if ($typeName === JsonResponse::class) {
            return [
                new OA\Response(response: 200, description: 'Успешный ответ'),
                new OA\Response(response: 401, description: 'Не авторизован'),
                new OA\Response(response: 403, description: 'Нет доступа'),
            ];
        }

        $reflect = new ReflectionClass($typeName);

        $properties = [];

        if ($reflect->isSubclassOf(self::BASE_RESPONSE_CLASS)) {
            $props = $reflect->getProperties();
            foreach ($props as $prop) {
                $type = OpenApiScalarTypesMapEnum::tryFrom((string) $prop->getType()); // get only defined types for openapi
                if ($type) {
                    $popertyAttributes = [
                        'property' => $prop->getName(),
                        'type' => $type->getPropertyType(),
                        'format' => $type->getPropertyFormat(),
                        'nullable' => $type->isNullable(),
                    ];
                    if ($type === OpenApiScalarTypesMapEnum::ARRAY || $type === OpenApiScalarTypesMapEnum::NULLABLE_ARRAY) {
                        $popertyAttributes['items'] = new OA\Items(anyOf: [
                            new OA\Schema(type: 'integer'),
                            new OA\Schema(type: 'string'),
                            // TODO: Add there object typing (array<array-key,SomeObject> || SomeObject)
                        ]);
                    }
                    $properties[] = new OA\Property(
                        ...$popertyAttributes,
                    );
                }
            }
        }

        $schemaName = $reflect->getShortName();

        $componentSchemas[] = new OA\Schema(
            schema: $schemaName,
            type: 'object',
            properties: $properties,
        );

        return [
            new OA\Response(
                response: 200,
                description: 'Успешный ответ',
                content: $properties ? new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'object',
                        properties: [
                            new OA\Property(
                                property: 'data',
                                type: 'object',
                                ref: "#/components/schemas/$schemaName",
                            ),
                        ],
                    )
                ) : null,
            ),
            new OA\Response(response: 401, description: 'Не авторизован'),
            new OA\Response(response: 403, description: 'Нет доступа'),
        ];
    }

    /**
     * @param array<array-key,mixed> $action
     */
    private function isClosure(array $action): bool
    {
        return isset($action[0]) && $action[0] instanceof Closure;
    }

    private function getReflectionMethod(string $uses): ReflectionMethod
    {
        [$className, $methodName] = explode('@', $uses);

        return new ReflectionMethod($className, $methodName);
    }

    /**
     * @param array<array-key,mixed> $action
     *
     * @return array<mixed>
     */
    private function getSecurity(array $action): array
    {
        if (
            isset($action['middleware'])
            && in_array(self::AUTH_MIDDLEWARE, $action['middleware'], true)
        ) {
            return [['bearerAuth' => []]];
        }

        return [];
    }
}
