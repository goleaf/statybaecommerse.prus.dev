<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class OpenApiSpecDriftTest extends TestCase
{
    public function test_openapi_spec_outputs_are_current(): void
    {
        $sourcePath = base_path('doc/api/catalog.yaml');
        $yamlPath = public_path('openapi.yaml');
        $jsonPath = public_path('openapi.json');

        $this->assertFileExists($sourcePath, 'The source OpenAPI YAML definition is missing.');
        $this->assertFileExists($yamlPath, 'Expected the committed OpenAPI YAML to exist.');
        $this->assertFileExists($jsonPath, 'Expected the committed OpenAPI JSON to exist.');

        /** @var array<string, mixed> $spec */
        $spec = \Symfony\Component\Yaml\Yaml::parseFile($sourcePath);

        $expectedYaml = \Symfony\Component\Yaml\Yaml::dump($spec, 20, 2, \Symfony\Component\Yaml\Yaml::DUMP_OBJECT_AS_MAP);
        $expectedJson = json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

        $this->assertSame($expectedYaml, File::get($yamlPath), 'Committed OpenAPI YAML is out of date. Regenerate from doc/api and recommit.');
        $this->assertSame($expectedJson, File::get($jsonPath), 'Committed OpenAPI JSON is out of date. Regenerate from doc/api and recommit.');
    }
}
