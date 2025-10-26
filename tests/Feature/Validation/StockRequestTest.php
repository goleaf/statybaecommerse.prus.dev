<?php

declare(strict_types=1);

namespace Tests\Feature\Validation;

use App\Models\VariantInventory;
use Tests\TestCase;

final class StockRequestTest extends TestCase
{
    public function test_adjust_stock_validation_fails_without_quantity(): void
    {
        $stock = VariantInventory::factory()->create();

        $this->postJson("/stock/{$stock->getKey()}/adjust", [])
            ->assertStatus(422);
    }

    public function test_reserve_stock_validation_fails_without_quantity(): void
    {
        $stock = VariantInventory::factory()->create();

        $this->postJson("/stock/{$stock->getKey()}/reserve", [])
            ->assertStatus(422);
    }

    public function test_unreserve_stock_validation_fails_without_quantity(): void
    {
        $stock = VariantInventory::factory()->create();

        $this->postJson("/stock/{$stock->getKey()}/unreserve", [])
            ->assertStatus(422);
    }
}
