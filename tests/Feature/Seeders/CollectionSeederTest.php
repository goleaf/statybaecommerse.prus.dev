<?php

declare(strict_types=1);

use App\Models\Collection;
use Database\Seeders\CollectionSeeder;
use Database\Seeders\Data\HouseBuilderCollections;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates non-empty active and visible collections', function (): void {
    (new CollectionSeeder)->run();

    $collections = Collection::query()->get();

    expect($collections->count())->toBeGreaterThan(0);
    expect($collections->every(static fn (Collection $collection): bool => (bool) $collection->is_active))->toBeTrue();
    expect($collections->every(static fn (Collection $collection): bool => (bool) $collection->is_visible))->toBeTrue();
});

it('is idempotent for collection slugs', function (): void {
    (new CollectionSeeder)->run();
    (new CollectionSeeder)->run();

    $expectedSlugs = array_keys(HouseBuilderCollections::collections());

    foreach ($expectedSlugs as $slug) {
        expect(Collection::withoutGlobalScopes()->where('slug', $slug)->count())->toBe(1);
    }
});
