<?php

declare(strict_types=1);

namespace App\Services;

use OpenApi\Analysis;
use OpenApi\Annotations\AbstractAnnotation;
use OpenApi\Annotations\Operation;
use OpenApi\Annotations\Parameter;
use OpenApi\Annotations\PathItem;
use OpenApi\Annotations\Property;
use OpenApi\Attributes as OA;
use OpenApi\Context;
use OpenApi\Generator;
use OpenApi\Serializer;
use Symfony\Component\HttpFoundation\File\Exception\NoFileException;

final class OpenApiMergeService
{
    private const SERVICES = './services.json';

    private array $services = [];

    public function __construct()
    {
        $this->getServices();
    }

    public function buildServicesDoc(Analysis $analysis): void
    {
        foreach ($this->services as $service) {
            $serviceAnalysis = $this->getServiceOpenapi($service->openapi);

            $this->buildServicePaths($serviceAnalysis->openapi->paths, $service->name);

            $analysis->openapi->paths = [
                ...$analysis->openapi->paths,
                ...$serviceAnalysis->openapi->paths,
            ];
            $analysis->openapi->tags[] = new OA\Tag(name: $service->name);

            foreach ($serviceAnalysis->openapi->components->schemas as $serviceComponentsSchema) {
                $analysis->openapi->components->schemas[] = $serviceComponentsSchema;
            }
        }
    }

    private function getServices(): void
    {
        $services = file_get_contents(self::SERVICES);

        if ($services === false) {
            throw new NoFileException('There no services file: ' . self::SERVICES);
        }

        $this->services = json_decode($services);
    }

    private function getServiceOpenapi($openapiFile): Analysis
    {
        return new Analysis(
            [
                $this->getAnnotation($openapiFile),
            ],
            new Context()
        ); // There we can do: $serviceAnalysis->openapi->saveAs("./{$service->name}.json", 'json');
    }

    private function getAnnotation(string $openapiFile): AbstractAnnotation
    {
        return (new Serializer())->deserializeFile($openapiFile, 'json', \OpenApi\Annotations\OpenApi::class);
    }

    /**
     * @param PathItem[] $servicePaths
     */
    private function buildServicePaths(array $servicePaths, string $serviceName): void
    {
        foreach ($servicePaths as $servicePath) {
            $this->buildServicePath($servicePath, $serviceName);
        }
    }

    private function buildServicePath(PathItem $servicePath, string $serviceName): void
    {
        $this->buildServicePathOperations($servicePath->operations(), $serviceName);

        $servicePath->path = "/api/{$serviceName}{$servicePath->path}";
    }

    /**
     * @param Operation[] $servicePathOperations
     */
    private function buildServicePathOperations(array $servicePathOperations, string $serviceName): void
    {
        foreach ($servicePathOperations as $operation) {
            $this->buildServicePathOperation($operation, $serviceName);
        }
    }

    private function buildServicePathOperation(Operation $servicePathOperation, string $serviceName): void
    {
        $servicePathOperation->tags = [$serviceName];

        $this->buildServicePathOperationSecurity($servicePathOperation);
    }

    private function buildServicePathOperationSecurity(Operation $servicePathOperation): void
    {
        $servicePathOperation->security = $this->isOperationRequestContainsUserData($servicePathOperation)
            ? [['bearerAuth' => []]]
            : [];
    }

    private function isOperationRequestContainsUserData(Operation $servicePathOperation): bool
    {

        $isInQuery = $this->isUserDataInQueryAndPathParams($servicePathOperation->parameters ?? []);
        $isInBody = $this->isUserDataInBodyParams($servicePathOperation->requestBody?->content['application/json']?->schema?->properties ?? []);

        return $isInQuery || $isInBody;
    }

    /**
     * @param Parameter[]|Generator::UNDEFINED $operationParameters
     */
    private function isUserDataInQueryAndPathParams(array|string $operationParameters): bool
    {
        if ($operationParameters === Generator::UNDEFINED) {
            return false;
        }

        foreach ($operationParameters as $operationParameter) {
            // TODO: check there in case $operationParameter->in === '' &&
            if (in_array($operationParameter->parameter, ['user_id', 'userId'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Property $requestBodyProperties
     */
    private function isUserDataInBodyParams(array $requestBodyProperties): bool
    {
        foreach ($requestBodyProperties as $requestBodyProperty) {
            if (in_array($requestBodyProperty->property, ['user_id', 'userId'], false)) {
                return true;
            }
        }

        return false;
    }
}
