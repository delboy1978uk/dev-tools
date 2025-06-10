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
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ResponseValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use League\OpenAPIValidation\Schema\SchemaValidator;
use function file_exists;
use function file_get_contents;
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
            $contents = file_get_contents($specFile);
            $type = strpos($specFile, '.json') !== false ? 'json' : 'yaml';
        }

        $this->spec = $type === 'json'
            ? Reader::readFromJson($contents)
            : Reader::readFromYaml($contents);

        if (!$this->spec) {
            $this->fail('No valid api doc yaml or json was provided');
        }

        $this->validator = (new ValidatorBuilder)->fromSchema($this->spec)->getResponseValidator();

    }

    public function seeValidApiSpec(string $method, string $route, array $responseBody, int $status = 200): void
    {
        try {
            $operation = new OperationAddress($route, $method);
            $response = new JsonResponse($responseBody, $status);
            $this->validator->validate($operation, $response);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
