<?php

declare(strict_types=1);

use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Boot Laravel's testing kernel for these Pest tests so factories and query
// scopes operate on a real database connection.
uses(TestCase::class, RefreshDatabase::class);

it('orders brands alphabetically by default', function (): void {
    // Create a small dataset with deliberately shuffled brand names.
    Brand::factory()->create(['name' => 'Zeta Tools']);
    Brand::factory()->create(['name' => 'Acme Builders']);
    Brand::factory()->create(['name' => 'Beta Works']);

    // Fetch the names using the new scope to confirm ascending ordering.
    $names = Brand::query()->withoutGlobalScopes()->orderedByName()->pluck('name')->all();

    // Verify that the resulting order is alphabetical from A to Z.
    expect($names)->toBe(['Acme Builders', 'Beta Works', 'Zeta Tools']);
});

it('supports ordering brands by name in descending order', function (): void {
    // Create a dataset with predictable alphabetical ordering to test DESC option.
    Brand::factory()->create(['name' => 'Atlas Co']);
    Brand::factory()->create(['name' => 'Nova Supplies']);
    Brand::factory()->create(['name' => 'Meridian Group']);

    // Leverage the scope with the desc flag to fetch the ordered collection.
    $names = Brand::query()->withoutGlobalScopes()->orderedByName('desc')->pluck('name')->all();

    // Confirm the data is returned in reverse alphabetical order.
    expect($names)->toBe(['Nova Supplies', 'Meridian Group', 'Atlas Co']);
});
