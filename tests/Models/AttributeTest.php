<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Attribute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AttributeTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_orders_attributes_by_name(): void
    {
        // Arrange: Create attributes in a deliberately unsorted order to validate the scope behaviour.
        $baseState = [
            // Ensure the records pass the default global scopes used by the model.
            'is_active'  => true,
            'is_enabled' => true,
            'is_visible' => true,
        ];

        $alpha = Attribute::factory()->create(array_merge($baseState, ['name' => 'Alpha Attribute']));
        $gamma = Attribute::factory()->create(array_merge($baseState, ['name' => 'Gamma Attribute']));
        $beta = Attribute::factory()->create(array_merge($baseState, ['name' => 'Beta Attribute']));

        // Act: Retrieve the attribute names using the orderedByName scope.
        $orderedNames = Attribute::query()
            ->orderedByName()
            ->pluck('name')
            ->all();

        // Assert: Confirm the results are alphabetically sorted regardless of creation order.
        $this->assertSame([
            $alpha->name,
            $beta->name,
            $gamma->name,
        ], $orderedNames);
    }
}
