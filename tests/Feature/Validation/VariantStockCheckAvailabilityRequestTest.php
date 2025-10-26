<?php

declare(strict_types=1);

namespace Tests\Feature\Validation;

use Tests\TestCase;

final class VariantStockCheckAvailabilityRequestTest extends TestCase
{
    public function test_missing_variant_id_returns_422(): void
    {
        $this->postJson('/variant-stock/check-availability', [])
            ->assertStatus(422);
    }
}
