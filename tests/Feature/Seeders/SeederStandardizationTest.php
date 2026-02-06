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
