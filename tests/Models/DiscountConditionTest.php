<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Category;
use App\Models\Discount;
use App\Models\DiscountCondition;
use App\Models\Product;
use App\Models\Translations\DiscountConditionTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DiscountConditionTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_are_configured(): void
    {
        // Arrange: create a condition with explicit values to check casting behaviour.
        $condition = DiscountCondition::factory()->create([
            'value' => ['min' => 100],
            'position' => 2,
            'is_active' => true,
            'priority' => 4,
            'metadata' => ['channel' => 'web'],
        ]);

        // Act: reload the model to ensure casts are applied when retrieving attributes.
        $condition->refresh();

        // Assert: confirm each attribute respects its intended data type.
        $this->assertIsArray($condition->value);
        $this->assertSame(['min' => 100], $condition->value);
        $this->assertIsInt($condition->position);
        $this->assertTrue($condition->is_active);
        $this->assertIsInt($condition->priority);
        $this->assertSame(['channel' => 'web'], $condition->metadata);
    }

    public function test_relationships_are_configured(): void
    {
        // Arrange: seed related models and pivot data for relationship verification.
        $discount = Discount::factory()->create();
        $product = Product::factory()->create();
        $category = Category::factory()->create();
        $condition = DiscountCondition::factory()->create(['discount_id' => $discount->id]);
        DiscountConditionTranslation::factory()->english()->create([
            'discount_condition_id' => $condition->id,
            'name' => 'Connected Condition',
        ]);
        $condition->products()->attach($product->id);
        $condition->categories()->attach($category->id);

        // Act: eager-load relations to avoid lazy loading noise inside assertions.
        $condition->load(['discount', 'products', 'categories', 'translations']);

        // Assert: ensure each relationship returns the expected models.
        $this->assertTrue($condition->discount->is($discount));
        $this->assertTrue($condition->products->contains($product));
        $this->assertTrue($condition->categories->contains($category));
        $this->assertSame('Connected Condition', $condition->translations->first()->name);
    }

    public function test_scopes_filter_and_order_records(): void
    {
        // Arrange: build a mixture of conditions to exercise the query scopes.
        $active = DiscountCondition::factory()->active()->create([
            'type' => 'cart_total',
            'operator' => 'greater_than',
            'priority' => 1,
        ]);
        $inactive = DiscountCondition::factory()->inactive()->create([
            'type' => 'product',
            'operator' => 'contains',
            'priority' => 5,
        ]);
        $additional = DiscountCondition::factory()->active()->create([
            'type' => 'product',
            'operator' => 'contains',
            'priority' => 3,
        ]);

        // Act: run the scopes under test to gather the filtered identifiers.
        $activeIds = DiscountCondition::query()->active()->pluck('id');
        $byTypeIds = DiscountCondition::query()->byType('product')->pluck('id');
        $byOperatorIds = DiscountCondition::query()->byOperator('contains')->pluck('id');
        $orderedByPriority = DiscountCondition::query()->byPriority('desc')->pluck('id');

        // Assert: validate each scope behaves as expected.
        $this->assertTrue($activeIds->contains($active->id));
        $this->assertFalse($activeIds->contains($inactive->id));
        $this->assertEqualsCanonicalizing([$inactive->id, $additional->id], $byTypeIds->all());
        $this->assertEqualsCanonicalizing([$inactive->id, $additional->id], $byOperatorIds->all());
        $this->assertSame([$inactive->id, $additional->id, $active->id], $orderedByPriority->all());
    }

    public function test_scope_ordered_by_name_uses_translation(): void
    {
        // Arrange: force locale awareness to line up with the translation records.
        app()->setLocale('en');
        $alpha = DiscountCondition::factory()->create();
        $beta = DiscountCondition::factory()->create();
        $gamma = DiscountCondition::factory()->create();

        DiscountConditionTranslation::factory()->english()->create([
            'discount_condition_id' => $alpha->id,
            'name' => 'Alpha Condition',
        ]);
        DiscountConditionTranslation::factory()->english()->create([
            'discount_condition_id' => $beta->id,
            'name' => 'Beta Condition',
        ]);
        DiscountConditionTranslation::factory()->english()->create([
            'discount_condition_id' => $gamma->id,
            'name' => 'Gamma Condition',
        ]);

        // Act: pull the ordered identifiers through the dedicated scope.
        $ordered = DiscountCondition::query()->orderedByName()->pluck('id')->all();

        // Assert: confirm alphabetical ordering respects the translation values.
        $this->assertSame([$alpha->id, $beta->id, $gamma->id], $ordered);
    }

    public function test_matches_and_context_validation_behaviour(): void
    {
        // Arrange: craft a simple numeric rule for repeated evaluation.
        $condition = DiscountCondition::factory()->create([
            'type' => 'cart_total',
            'operator' => 'greater_than',
            'value' => 100,
            'is_active' => true,
        ]);

        // Act & Assert: evaluate raw matching scenarios.
        $this->assertTrue($condition->matches(150));
        $this->assertFalse($condition->matches(90));

        // Act & Assert: confirm contextual validation mirrors match outcomes.
        $this->assertTrue($condition->isValidForContext(['cart_total' => 150]));
        $this->assertFalse($condition->isValidForContext(['cart_total' => 90]));
        $this->assertFalse($condition->isValidForContext([]));
    }

    public function test_translated_accessors_and_human_readable_attribute(): void
    {
        // Arrange: seed a translation to exercise the accessor helpers.
        app()->setLocale('en');
        $condition = DiscountCondition::factory()->create([
            'type' => 'product',
            'operator' => 'equals_to',
            'value' => 'VIP',
            'is_active' => true,
        ]);
        DiscountConditionTranslation::factory()->english()->create([
            'discount_condition_id' => $condition->id,
            'name' => 'VIP Customers',
            'description' => 'Only VIP customers qualify for this offer.',
        ]);

        // Act: access the computed properties and helper labels.
        $translatedName = $condition->translated_name;
        $translatedDescription = $condition->translated_description;
        $humanReadable = $condition->human_readable_condition;
        $typeLabel = $condition->getTypeLabel();
        $operatorLabel = $condition->getOperatorLabel();
        $types = DiscountCondition::getTypes();
        $operators = DiscountCondition::getOperators();
        $typeSpecificOperators = DiscountCondition::getOperatorsForType('product');

        // Assert: ensure human-facing helpers surface meaningful content.
        $this->assertSame('VIP Customers', $translatedName);
        $this->assertSame('Only VIP customers qualify for this offer.', $translatedDescription);
        $this->assertStringContainsString('VIP', $humanReadable);
        $this->assertNotEmpty($typeLabel);
        $this->assertNotEmpty($operatorLabel);
        $this->assertArrayHasKey('product', $types);
        $this->assertArrayHasKey('equals_to', $operators);
        $this->assertArrayHasKey('equals_to', $typeSpecificOperators);
    }
}
