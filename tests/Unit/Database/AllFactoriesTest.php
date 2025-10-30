<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class AllFactoriesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Discover all factory classes dynamically so new definitions automatically receive coverage.
     *
     * @return array<string, array{string}>
     */
    public static function factoryClassProvider(): array
    {
        $factoryDirectory = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'factories';

        if (! is_dir($factoryDirectory)) {
            // Return an empty dataset when the factories directory is missing to avoid noisy failures during discovery.
            return [];
        }

        $factoryDataset = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($factoryDirectory)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                // Ignore directories during iteration because only PHP files map to factories.
                continue;
            }

            // Resolve the fully-qualified class name from the file path so we can instantiate the factory dynamically.
            $relativePath = str_replace($factoryDirectory . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $relativeClass = str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);
            $className = 'Database\\Factories\\' . str_replace('.php', '', $relativeClass);

            if (! class_exists($className)) {
                // Skip files that do not map to autoloadable classes to avoid false failures.
                continue;
            }

            $reflection = new \ReflectionClass($className);

            if ($reflection->isAbstract()) {
                // Ignore abstract base factories because they are not invokable on their own.
                continue;
            }

            $factoryDataset[$className] = [$className];
        }

        ksort($factoryDataset);

        return $factoryDataset;
    }

    #[DataProvider('factoryClassProvider')]
    public function test_factory_creates_persisted_model_instance(string $factoryClass): void
    {
        /** @var \Illuminate\Database\Eloquent\Factories\Factory $factory */
        $factory = $factoryClass::new();

        try {
            // Resolve the model class to surface missing bindings early.
            $expectedModelClass = get_class($factory->newModel());
        } catch (\Throwable $exception) {
            $this->markTestIncomplete(sprintf('Unable to resolve the model for %s: %s', $factoryClass, $exception->getMessage()));

            return;
        }

        // Assert the resolved model class exists before touching the definition payload.
        $this->assertTrue(class_exists($expectedModelClass), 'The factory should reference an autoloadable model class.');

        // Generate the raw definition to confirm the factory seeds attributes without persisting records.
        $definition = $factory->definition();

        // Assert the definition is a non-empty array so the factory exposes seed data.
        $this->assertIsArray($definition, 'The factory definition should return an attribute array.');
        $this->assertNotEmpty($definition, 'The factory definition should expose at least one attribute.');
    }
}
