<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Seeder that automatically executes every seeder class found in the database/seeders directory.
 */
final class AllSeedersSeeder extends Seeder
{
    /**
     * Core seeders that must run before the remaining classes to satisfy dependencies.
     *
     * @var array<int, class-string<Seeder>>
     */
    private const PRIORITY_SEEDERS = [
        CurrencySeeder::class,
        AttributeSeeder::class,
        AttributeValueSeeder::class,
        AdminAuthorizationSeeder::class,
        RolesAndPermissionsSeeder::class,
        AdminUserSeeder::class,
        CustomerGroupSeeder::class,
    ];

    /**
     * Execute the database seeds by discovering and running every seeder class.
     */
    public function run(): void
    {
        // Gather every concrete seeder class within the seeders directory tree.
        $discoveredSeeders = $this->discoverSeeders();

        // Run the priority seeders first to guarantee dependent data is available.
        foreach (self::PRIORITY_SEEDERS as $prioritySeeder) {
            if (isset($discoveredSeeders[$prioritySeeder])) {
                $this->call($prioritySeeder);
                unset($discoveredSeeders[$prioritySeeder]);
            }
        }

        // Execute the remaining seeders in alphabetical order for deterministic runs.
        ksort($discoveredSeeders);

        foreach ($discoveredSeeders as $seederClass) {
            $this->call($seederClass);
        }
    }

    /**
     * Locate every Seeder subclass defined under database/seeders.
     *
     * @return array<class-string<Seeder>, class-string<Seeder>>
     */
    private function discoverSeeders(): array
    {
        $seeders = [];
        $directoryIterator = new RecursiveDirectoryIterator(database_path('seeders'));
        $fileIterator = new RecursiveIteratorIterator($directoryIterator);

        /** @var SplFileInfo $file */
        foreach ($fileIterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $className = $this->resolveClassName($file);

            if ($className === null) {
                continue;
            }

            if ($className === self::class || $className === DatabaseSeeder::class) {
                // Skip the aggregator itself and the DatabaseSeeder to avoid recursion.
                continue;
            }

            if (! is_subclass_of($className, Seeder::class)) {
                // Ignore helper classes that do not extend the Seeder base class.
                continue;
            }

            $seeders[$className] = $className;
        }

        return $seeders;
    }

    /**
     * Build the fully-qualified class name for a given seeder file.
     */
    private function resolveClassName(SplFileInfo $file): ?string
    {
        $contents = file_get_contents($file->getRealPath());

        if ($contents === false) {
            return null;
        }

        if (! preg_match('/^namespace\s+([^;]+);/m', $contents, $namespaceMatch)) {
            return null;
        }

        if (! preg_match('/^\s*(?:final\s+)?class\s+(\w+)/m', $contents, $classMatch)) {
            return null;
        }

        $namespace = trim($namespaceMatch[1]);
        $class = trim($classMatch[1]);

        // Compose the namespaced class string using the parsed namespace and class name.
        return sprintf('%s\\%s', $namespace, $class);
    }
}
