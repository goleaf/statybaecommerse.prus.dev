<?php

declare(strict_types=1);

use App\Models\Location;
use Database\Seeders\WarehouseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates default warehouse locations with warehouse type', function () {
    (new WarehouseSeeder)->run();

    $warehouses = Location::query()
        ->where('type', 'warehouse')
        ->orderBy('code')
        ->get();

    expect($warehouses->count())->toBeGreaterThanOrEqual(2);
    expect($warehouses->pluck('code')->all())->toContain('WH-001');
    expect($warehouses->pluck('code')->all())->toContain('WH-002');
});

it('is idempotent and does not duplicate warehouse records', function () {
    (new WarehouseSeeder)->run();
    (new WarehouseSeeder)->run();

    expect(Location::query()->where('type', 'warehouse')->where('code', 'WH-001')->count())->toBe(1);
    expect(Location::query()->where('type', 'warehouse')->where('code', 'WH-002')->count())->toBe(1);
});
