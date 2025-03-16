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
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Exception;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use League\OpenAPIValidation\Schema\SchemaValidator;
use function file_exists;
use function file_get_contents;
use function strpos;

class ApiValidator extends Module
{
    private ?OpenApi $spec = null;
    private string $type;
    private string $contents;

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
            $this->contents = file_get_contents($specFile);
            $this->type = strpos($specFile, '.json') !== false ? 'json' : 'yaml';
        }
    }

    public function seeValidApiSpec(string $method, string $route, string $responseBody, int $status = 200): void
    {
        if (!$this->contents) {
            $this->fail('No valid api doc was specified');
        }

        try {
            $this->spec = $this->type === 'json'
                ? Reader::readFromJson($this->contents)
                : Reader::readFromYaml($this->contents);
            $validator = (new ValidatorBuilder)->fromSchema($this->spec)->getResponseValidator();
            $operation = new OperationAddress($route, $method);
            $response = new JsonResponse($responseBody, $status);
            $validator->validate($operation, $response);
            $this->assertNotTrue(false);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
