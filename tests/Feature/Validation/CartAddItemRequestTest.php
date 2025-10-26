<?php

declare(strict_types=1);

namespace Tests\Feature\Validation;

use Tests\TestCase;

final class CartAddItemRequestTest extends TestCase
{
    public function test_add_item_requires_product_and_quantity_returns_422(): void
    {
        $this->postJson('/cart/items', [])
            ->assertStatus(422);
    }
}
