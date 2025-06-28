<?php

declare(strict_types=1);

namespace Codeception\Module;

use Bone\Application;
use Bone\Http\Response\JsonResponse;
use Bone\Server\SiteConfig;
use cebe\openapi\Reader;
use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Schema;
use cebe\openapi\SpecObjectInterface;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Exception;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\Stream;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ResponseValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use League\OpenAPIValidation\Schema\SchemaValidator;
use function file_exists;
use function file_get_contents;
use function json_decode;
use function strpos;

class ApiValidator extends Module
{
    private ?SpecObjectInterface $spec = null;

    public function __construct(protected ModuleContainer $moduleContainer, ?array $config = null)
    {
        parent::__construct($moduleContainer, $config);
        $configPath = 'config/bone-open-api.php';

        if (file_exists($configPath)) {
            $config = include $configPath;
            $specFile = $config['docs'];
        } else {
            $specFile = 'data/docs/api.json';
        }

        if (file_exists($specFile)) {
            $type = strpos($specFile, '.json') !== false ? 'json' : 'yaml';
        }

        $this->validator = $type === 'json'
            ? (new ValidatorBuilder)->fromJsonFile($specFile)->getResponseValidator()
            : (new ValidatorBuilder)->fromYamlFile($specFile)->getResponseValidator();
    }

    public function seeValidApiSpec(string $method, string $route, string $responseBody, int $status = 200): void
    {
        try {
            $body = json_decode($responseBody, true);
            $operation = new OperationAddress($route, $method);
            $response = new JsonResponse($body, $status);
            $this->validator->validate($operation, $response);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
