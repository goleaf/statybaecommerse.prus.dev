<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Discount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversMethod;
use Tests\TestCase;

// Ensure PHPUnit tracks the scope coverage using the modern attribute syntax.
#[CoversMethod(Discount::class, 'scopeOrderedByName')]
final class DiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_ordered_by_name_sorts_alphabetically(): void
    {
        // Create intentionally unsorted discounts so we can prove the scope reorders them.
        Discount::factory()->create(['name' => 'Zebra Savings']);
        Discount::factory()->create(['name' => 'Alpha Deal']);
        Discount::factory()->create(['name' => 'Midnight Offer']);

        // Run the scope and capture the ordered results for verification.
        $names = Discount::query()
            ->orderedByName()
            ->pluck('name')
            ->all();

        // Validate that the resulting sequence is alphabetically sorted.
        $this->assertSame(['Alpha Deal', 'Midnight Offer', 'Zebra Savings'], $names);
    }

    public function test_scope_ordered_by_name_supports_descending_direction(): void
    {
        // Seed the database with deterministic names to check descending sorting.
        Discount::factory()->create(['name' => 'Blue Bonus']);
        Discount::factory()->create(['name' => 'Crimson Coupon']);
        Discount::factory()->create(['name' => 'Amber Advantage']);

        // Retrieve the names using the descending option to verify defensive direction handling.
        $names = Discount::query()
            ->orderedByName('desc')
            ->pluck('name')
            ->all();

        // Confirm that the names are returned in descending alphabetical order.
        $this->assertSame(['Crimson Coupon', 'Blue Bonus', 'Amber Advantage'], $names);
    }

    public function test_scope_ordered_by_name_invalid_direction_defaults_to_ascending(): void
    {
        // Insert out-of-order names so we can see the sanitised direction take effect.
        Discount::factory()->create(['name' => 'Cedar Special']);
        Discount::factory()->create(['name' => 'Aspen Savings']);

        // Call the scope with an invalid direction string to hit the guard clause.
        $names = Discount::query()
            ->orderedByName('sideways')
            ->pluck('name')
            ->all();

        // Ensure the fallback keeps the results in ascending order for stability.
        $this->assertSame(['Aspen Savings', 'Cedar Special'], $names);
    }
}
