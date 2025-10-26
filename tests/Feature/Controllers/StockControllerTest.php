<?php

declare(strict_types=1);

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use Illuminate\Support\Carbon;

// Ensure Pest helpers are available for HTTP assertions.
use function Pest\Laravel\get;

it('groups search constraints so additional filters remain effective', function (): void {
    // Create two enabled locations to simulate separate warehouses for filtering.
    $primaryLocation = Location::factory()->enabled()->create(['name' => 'Primary Warehouse']);
    $secondaryLocation = Location::factory()->enabled()->create(['name' => 'Secondary Warehouse']);

    // Reuse the same search term across products so the filter would match both without grouping.
    $searchableName = 'Shared Inventory Drill';

    $firstVariant = ProductVariant::factory()
        ->for(Product::factory()->create(['name' => $searchableName]))
        ->create(['name' => 'Shared Variant A', 'sku' => 'SHARED-A']);

    $secondVariant = ProductVariant::factory()
        ->for(Product::factory()->create(['name' => $searchableName]))
        ->create(['name' => 'Shared Variant B', 'sku' => 'SHARED-B']);

    // Persist inventories with identical stock but different locations to validate the constraint.
    $matchingInventory = VariantInventory::factory()
        ->for($firstVariant, 'variant')
        ->for($primaryLocation, 'location')
        ->active()
        ->create(['stock' => 40, 'reserved' => 5, 'available' => 35]);

    $nonMatchingInventory = VariantInventory::factory()
        ->for($secondVariant, 'variant')
        ->for($secondaryLocation, 'location')
        ->active()
        ->create(['stock' => 45, 'reserved' => 10, 'available' => 35]);

    $response = get(route('stock.index', ['search' => 'Shared', 'location_id' => $primaryLocation->id]));

    $response->assertOk();

    $paginator = $response->viewData('stockItems');
    $visibleIds = collect($paginator->items())->pluck('id');

    expect($visibleIds)->toContain($matchingInventory->getKey());
    expect($visibleIds)->not()->toContain($nonMatchingInventory->getKey());
});

it('falls back to the default sorting when unknown options are provided', function (): void {
    // Stabilise timestamps so we can predict the pagination order reliably.
    Carbon::setTestNow('2024-02-01 10:00:00');
    $location = Location::factory()->enabled()->create();

    $olderVariant = ProductVariant::factory()
        ->for(Product::factory())
        ->create(['name' => 'Chronos Variant', 'sku' => 'TIME-OLD']);

    $olderInventory = VariantInventory::factory()
        ->for($olderVariant, 'variant')
        ->for($location, 'location')
        ->active()
        ->create();

    // Advance the clock to make the next record newer for descending order checks.
    Carbon::setTestNow('2024-02-02 10:00:00');
    $newerVariant = ProductVariant::factory()
        ->for(Product::factory())
        ->create(['name' => 'Chronos Variant New', 'sku' => 'TIME-NEW']);

    $newerInventory = VariantInventory::factory()
        ->for($newerVariant, 'variant')
        ->for($location, 'location')
        ->active()
        ->create();

    Carbon::setTestNow();

    $response = get(route('stock.index', ['sort_by' => 'DROP TABLE', 'sort_direction' => 'invalid direction']));

    $response->assertOk();

    $paginator = $response->viewData('stockItems');
    $idsInOrder = collect($paginator->items())->pluck('id');

    expect($idsInOrder->first())->toBe($newerInventory->getKey());
    expect($idsInOrder)->toContain($olderInventory->getKey());
});
