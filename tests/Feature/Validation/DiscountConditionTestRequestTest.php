<?php

declare(strict_types=1);

namespace Tests\Feature\Validation;

use App\Models\DiscountCondition;
use Tests\TestCase;

final class DiscountConditionTestRequestTest extends TestCase
{
    public function test_missing_test_value_returns_422(): void
    {
        $condition = DiscountCondition::factory()->create();

        $this->postJson("/discount-conditions/{$condition->getKey()}/test", [])
            ->assertStatus(422);
    }
}
