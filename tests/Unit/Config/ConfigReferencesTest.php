<?php

declare(strict_types=1);

use Illuminate\Database\Seeder;

it('unit: filament navigation references existing classes', function (): void {
    $resources = config('filament.navigation.resources', []);
    $pages = config('filament.navigation.pages', []);

    expect($resources)->toBeArray();
    expect($pages)->toBeArray();

    foreach (array_merge($resources, $pages) as $class) {
        expect($class)->toBeString();
        expect(class_exists($class))->toBeTrue();
    }
});

it('unit: model scopes only reference existing model classes', function (): void {
    $scopes = config('model-scopes');

    expect($scopes)->toBeArray();

    foreach ($scopes as $scopeClass => $modelClasses) {
        expect($scopeClass)->toBeString();
        expect(class_exists($scopeClass))->toBeTrue();
        expect($modelClasses)->toBeArray();

        foreach ($modelClasses as $modelClass) {
            expect($modelClass)->toBeString();
            expect(class_exists($modelClass))->toBeTrue();
        }
    }
});

it('unit: seed profiles reference existing seeders', function (): void {
    $profiles = config('seeds.profiles');

    expect($profiles)->toBeArray();

    foreach ($profiles as $profile => $seeders) {
        expect($profile)->toBeString();
        expect($seeders)->toBeArray();

        foreach ($seeders as $seederClass) {
            expect($seederClass)->toBeString();
            expect(class_exists($seederClass))->toBeTrue();
            expect(is_subclass_of($seederClass, Seeder::class))->toBeTrue();
        }
    }
});
