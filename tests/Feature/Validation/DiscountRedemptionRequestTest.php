<?php

declare(strict_types=1);

namespace Tests\Feature\Validation;

use App\Models\User;
use Tests\TestCase;

final class DiscountRedemptionRequestTest extends TestCase
{
    public function test_store_requires_discount_code_returns_422(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->postJson('/discount-redemptions', [])
            ->assertStatus(422);
    }
}
