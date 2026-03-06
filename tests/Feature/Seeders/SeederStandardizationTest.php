<?php

declare(strict_types=1);

use Database\Seeders\BaseSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

test('all concrete seeders extend the shared base seeder', function (): void {
    $nonStandardSeeders = [];

    foreach (File::allFiles(database_path('seeders')) as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $contents = file_get_contents($file->getPathname());
        if ($contents === false) {
            continue;
        }

        if (! preg_match('/^namespace\\s+([^;]+);/m', $contents, $namespaceMatch)) {
            continue;
        }

        if (! preg_match('/^\\s*(?:final\\s+)?class\\s+(\\w+)/m', $contents, $classMatch)) {
            continue;
        }

        $class = trim($namespaceMatch[1]) . '\\' . trim($classMatch[1]);

        if (! class_exists($class) || $class === BaseSeeder::class) {
            continue;
        }

        if (! is_subclass_of($class, Seeder::class)) {
            continue;
        }

        if (! is_subclass_of($class, BaseSeeder::class)) {
            $nonStandardSeeders[] = $class;
        }
    }

    expect($nonStandardSeeders)->toBe([]);
});

test('standard seeder registry is unique and valid', function (): void {
    $standardSeeders = config('seeds.standard_seeders');

    expect($standardSeeders)
        ->toBeArray()
        ->not->toBeEmpty();

    $duplicates = collect($standardSeeders)
        ->duplicates()
        ->values()
        ->all();

    $invalid = collect($standardSeeders)
        ->filter(static fn (mixed $class): bool => ! is_string($class) || ! class_exists($class))
        ->values()
        ->all();

    expect($duplicates)->toBe([]);
    expect($invalid)->toBe([]);
});

test('standard seeders keep model ownership overlap minimal and explicit', function (): void {
    $allowedOverlaps = [
        'Attribute' => [
            'Database\\Seeders\\AttributeSeeder',
            'Database\\Seeders\\AttributeValueSeeder',
        ],
        'Country' => [
            'Database\\Seeders\\CountrySeeder',
            'Database\\Seeders\\Cities\\CitiesMergedSeeder',
        ],
        'Company' => [
            'Database\\Seeders\\UsersCustomerGroupsTabSeeder',
            'Database\\Seeders\\UsersPartnersTabSeeder',
        ],
        'CustomerGroup' => [
            'Database\\Seeders\\CustomerGroupSeeder',
            'Database\\Seeders\\UsersCustomerGroupsTabSeeder',
        ],
        'Location' => [
            'Database\\Seeders\\InventorySeeder',
            'Database\\Seeders\\WarehouseSeeder',
        ],
        'User' => [
            'Database\\Seeders\\UsersCustomerGroupsTabSeeder',
            'Database\\Seeders\\UsersPartnersTabSeeder',
        ],
    ];

    $classes = config('seeds.standard_seeders', []);
    $modelsByClass = [];

    foreach ($classes as $class) {
        if (! is_string($class) || ! class_exists($class)) {
            continue;
        }

        $file = (new \ReflectionClass($class))->getFileName();

        if (! is_string($file) || ! file_exists($file)) {
            continue;
        }

        $modelsByClass[$class] = collect(file($file) ?: [])
            ->map(static fn (string $line): string => trim($line))
            ->map(static function (string $line): ?string {
                if (! preg_match('/^use\\s+App\\\\Models\\\\([^;]+);/', $line, $matches)) {
                    return null;
                }

                return $matches[1];
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    $ownersByModel = [];

    foreach ($modelsByClass as $class => $models) {
        foreach ($models as $model) {
            $ownersByModel[$model][] = $class;
        }
    }

    $unexpectedOverlaps = collect($ownersByModel)
        ->filter(static fn (array $owners): bool => count($owners) > 1)
        ->map(static fn (array $owners): array => array_values(array_unique($owners)))
        ->reject(static function (array $owners, string $model) use ($allowedOverlaps): bool {
            $allowed = $allowedOverlaps[$model] ?? null;
            if (! is_array($allowed)) {
                return false;
            }

            sort($owners);
            sort($allowed);

            return $owners === $allowed;
        })
        ->all();

    expect($unexpectedOverlaps)->toBe([]);
});
