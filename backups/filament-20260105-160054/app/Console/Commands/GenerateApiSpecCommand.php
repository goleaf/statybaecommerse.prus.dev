<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use const JSON_THROW_ON_ERROR;

use OpenApi\Generator;
use Psr\Log\AbstractLogger;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Throwable;

final class GenerateApiSpecCommand extends Command
{
    protected $signature = 'api:spec';

    protected $description = 'Generate OpenAPI specification files for the public API.';

    public function handle(): int
    {
        $this->info('🔄 Generating OpenAPI specification...');

        $annotationPaths = $this->discoverAnnotationPaths();
        $yamlFiles = $this->discoverYamlFiles();

        if ($annotationPaths === [] && $yamlFiles === []) {
            throw new RuntimeException('No OpenAPI sources were found. Add annotations or YAML files before running the command.');
        }

        $specData = [];

        if ($annotationPaths !== []) {
            try {
                $specData = $this->generateFromAnnotations($annotationPaths);
            } catch (Throwable $exception) {
                throw new RuntimeException(
                    'Failed to generate OpenAPI specification from PHP annotations: ' . $exception->getMessage(),
                    0,
                    $exception,
                );
            }
        }

        foreach ($yamlFiles as $yamlFile) {
            try {
                $yamlData = Yaml::parseFile($yamlFile);
            } catch (Throwable $exception) {
                throw new RuntimeException(
                    "Failed to parse OpenAPI YAML source [{$yamlFile}]: " . $exception->getMessage(),
                    0,
                    $exception,
                );
            }

            if (is_array($yamlData)) {
                $specData = $this->mergeSpec($specData, $yamlData);
            }
        }

        if ($specData === []) {
            throw new RuntimeException('The generated OpenAPI specification is empty.');
        }

        $this->writeOutputFiles($specData);

        $this->info('✅ OpenAPI specification successfully generated.');

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function discoverAnnotationPaths(): array
    {
        $candidates = [
            app_path('Http/Controllers/Api'),
            app_path('Http/Controllers'),
            app_path('Data'),
            app_path('Enums'),
            base_path('routes/api.php'),
        ];

        $paths = [];

        foreach ($candidates as $candidate) {
            if (File::exists($candidate)) {
                $paths[] = $candidate;
            }
        }

        return array_values(array_unique($paths));
    }

    /**
     * @param  array<int, string>   $paths
     * @return array<string, mixed>
     */
    private function generateFromAnnotations(array $paths): array
    {
        /** @var array<int, array{level: string, message: string}> $messages */
        $messages = [];

        $logger = new class($messages) extends AbstractLogger
        {
            /**
             * @var array<int, array{level: string, message: string}>
             */
            private $messagesRef;

            /**
             * @param array<int, array{level: string, message: string}> $messages
             */
            public function __construct(array &$messages)
            {
                $this->messagesRef = &$messages;
            }

            public function log($level, $message, array $context = []): void
            {
                $message = is_string($message) ? $message : (string) $message;

                foreach ($context as $key => $value) {
                    $message = str_replace('{' . $key . '}', (string) $value, $message);
                }

                $this->messagesRef[] = [
                    'level'   => (string) $level,
                    'message' => $message,
                ];
            }
        };

        $openApi = Generator::scan($paths, ['logger' => $logger]);

        $this->assertNoGenerationWarnings($messages);

        return json_decode($openApi->toJson(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param array<int, array{level: string, message: string}> $messages
     */
    private function assertNoGenerationWarnings(array $messages): void
    {
        $issues = array_filter(
            $messages,
            static fn (array $entry): bool => in_array(
                $entry['level'],
                ['warning', 'error', 'critical', 'alert', 'emergency'],
                true,
            ),
        );

        if ($issues === []) {
            return;
        }

        $formatted = $this->formatMessages(array_values($issues));

        throw new RuntimeException('OpenAPI generation reported issues:' . PHP_EOL . implode(PHP_EOL, $formatted));
    }

    /**
     * @param  array<int, array{level: string, message: string}> $messages
     * @return array<int, string>
     */
    private function formatMessages(array $messages): array
    {
        return array_map(
            static fn (array $entry): string => '[' . strtoupper($entry['level']) . '] ' . $entry['message'],
            $messages,
        );
    }

    /**
     * @return array<int, string>
     */
    private function discoverYamlFiles(): array
    {
        $directories = [
            base_path('docs/api'),
            base_path('docs/openapi'),
            base_path('doc/api'),
            resource_path('openapi'),
            base_path('openapi'),
        ];

        $files = [];

        foreach ($directories as $directory) {
            if (! File::isDirectory($directory)) {
                continue;
            }

            foreach (File::files($directory) as $file) {
                if (in_array($file->getExtension(), ['yaml', 'yml'], true)) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    /**
     * @param  array<string, mixed> $base
     * @param  array<string, mixed> $merge
     * @return array<string, mixed>
     */
    private function mergeSpec(array $base, array $merge): array
    {
        foreach ($merge as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = $this->mergeSpec($base[$key], $value);

                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }

    /**
     * @param array<string, mixed> $specData
     */
    private function writeOutputFiles(array $specData): void
    {
        try {
            $json = json_encode($specData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            throw new RuntimeException('Failed to encode OpenAPI specification as JSON: ' . $exception->getMessage(), 0, $exception);
        }

        try {
            $yaml = Yaml::dump($specData, 20, 2, Yaml::DUMP_OBJECT_AS_MAP);
        } catch (Throwable $exception) {
            throw new RuntimeException('Failed to encode OpenAPI specification as YAML: ' . $exception->getMessage(), 0, $exception);
        }

        $destination = public_path();
        File::ensureDirectoryExists($destination);

        try {
            File::put($destination . '/openapi.json', $json);
            File::put($destination . '/openapi.yaml', $yaml);
        } catch (Throwable $exception) {
            throw new RuntimeException('Failed to write OpenAPI specification files: ' . $exception->getMessage(), 0, $exception);
        }
    }
}
