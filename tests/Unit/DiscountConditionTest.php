<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Discount;
use App\Models\DiscountCondition;
use App\Models\Translations\DiscountConditionTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DiscountConditionTest extends TestCase
{
    use RefreshDatabase;

    public function test_discount_condition_can_be_created(): void
    {
        $discount = Discount::factory()->create();
        
        $condition = DiscountCondition::create([
            'discount_id' => $discount->id,
            'type' => 'cart_total',
            'operator' => 'greater_than',
            'value' => 100,
            'position' => 1,
            'is_active' => true,
            'priority' => 5,
            'metadata' => ['test' => 'data'],
        ]);

        $this->assertDatabaseHas('discount_conditions', [
            'id' => $condition->id,
            'discount_id' => $discount->id,
            'type' => 'cart_total',
            'operator' => 'greater_than',
            'value' => json_encode(100),
            'position' => 1,
            'is_active' => true,
            'priority' => 5,
        ]);
    }

    public function test_discount_condition_belongs_to_discount(): void
    {
        $discount = Discount::factory()->create();
        $condition = DiscountCondition::factory()->create(['discount_id' => $discount->id]);

        $this->assertInstanceOf(Discount::class, $condition->discount);
        $this->assertEquals($discount->id, $condition->discount->id);
    }

    public function test_discount_condition_has_translations(): void
    {
        $condition = DiscountCondition::factory()->create();
        
        $translation = DiscountConditionTranslation::create([
            'discount_condition_id' => $condition->id,
            'locale' => 'lt',
            'name' => 'Test Condition',
            'description' => 'Test Description',
        ]);

        $this->assertTrue($condition->translations()->exists());
        $this->assertEquals('Test Condition', $condition->translations->first()->name);
    }

    public function test_discount_condition_matches_numeric_values(): void
    {
        $condition = DiscountCondition::factory()->create([
            'type' => 'cart_total',
            'operator' => 'greater_than',
            'value' => 100,
            'is_active' => true,
        ]);

        $this->assertTrue($condition->matches(150));
        $this->assertFalse($condition->matches(50));
    }

    public function test_discount_condition_matches_string_values(): void
    {
        $condition = DiscountCondition::factory()->create([
            'type' => 'product',
            'operator' => 'contains',
            'value' => 'test',
            'is_active' => true,
        ]);

        $this->assertTrue($condition->matches('test product'));
        $this->assertFalse($condition->matches('other product'));
    }

    public function test_discount_condition_matches_array_values(): void
    {
        $condition = DiscountCondition::factory()->create([
            'type' => 'category',
            'operator' => 'in_array',
            'value' => ['electronics', 'clothing'],
            'is_active' => true,
        ]);

        $this->assertTrue($condition->matches('electronics'));
        $this->assertFalse($condition->matches('books'));
    }

    public function test_discount_condition_does_not_match_when_inactive(): void
    {
        $condition = DiscountCondition::factory()->create([
            'type' => 'cart_total',
            'operator' => 'greater_than',
            'value' => 100,
            'is_active' => false,
        ]);

        $this->assertFalse($condition->matches(150));
    }

    public function test_discount_condition_is_valid_for_context(): void
    {
        $condition = DiscountCondition::factory()->create([
            'type' => 'cart_total',
            'operator' => 'greater_than',
            'value' => 100,
            'is_active' => true,
        ]);

        $context = ['cart_total' => 150];
        $this->assertTrue($condition->isValidForContext($context));

        $context = ['cart_total' => 50];
        $this->assertFalse($condition->isValidForContext($context));

        $context = ['other_field' => 150];
        $this->assertFalse($condition->isValidForContext($context));
    }

    public function test_discount_condition_scope_active(): void
    {
        DiscountCondition::factory()->create(['is_active' => true]);
        DiscountCondition::factory()->create(['is_active' => false]);

        $activeConditions = DiscountCondition::active()->get();
        
        $this->assertCount(1, $activeConditions);
        $this->assertTrue($activeConditions->first()->is_active);
    }

    public function test_discount_condition_scope_by_type(): void
    {
        DiscountCondition::factory()->create(['type' => 'cart_total']);
        DiscountCondition::factory()->create(['type' => 'product']);

        $cartConditions = DiscountCondition::byType('cart_total')->get();
        
        $this->assertCount(1, $cartConditions);
        $this->assertEquals('cart_total', $cartConditions->first()->type);
    }

    public function test_discount_condition_scope_by_operator(): void
    {
        DiscountCondition::factory()->create(['operator' => 'greater_than']);
        DiscountCondition::factory()->create(['operator' => 'equals_to']);

        $greaterThanConditions = DiscountCondition::byOperator('greater_than')->get();
        
        $this->assertCount(1, $greaterThanConditions);
        $this->assertEquals('greater_than', $greaterThanConditions->first()->operator);
    }

    public function test_discount_condition_scope_by_priority(): void
    {
        DiscountCondition::factory()->create(['priority' => 1]);
        DiscountCondition::factory()->create(['priority' => 10]);

        $conditions = DiscountCondition::byPriority('asc')->get();
        
        $this->assertEquals(1, $conditions->first()->priority);
        $this->assertEquals(10, $conditions->last()->priority);
    }

    public function test_discount_condition_get_types(): void
    {
        $types = DiscountCondition::getTypes();
        
        $this->assertIsArray($types);
        $this->assertArrayHasKey('product', $types);
        $this->assertArrayHasKey('cart_total', $types);
        $this->assertArrayHasKey('category', $types);
    }

    public function test_discount_condition_get_operators(): void
    {
        $operators = DiscountCondition::getOperators();
        
        $this->assertIsArray($operators);
        $this->assertArrayHasKey('equals_to', $operators);
        $this->assertArrayHasKey('greater_than', $operators);
        $this->assertArrayHasKey('contains', $operators);
    }

    public function test_discount_condition_get_operators_for_type(): void
    {
        $numericOperators = DiscountCondition::getOperatorsForType('cart_total');
        $stringOperators = DiscountCondition::getOperatorsForType('product');
        
        $this->assertArrayHasKey('greater_than', $numericOperators);
        $this->assertArrayHasKey('less_than', $numericOperators);
        $this->assertArrayNotHasKey('contains', $numericOperators);
        
        $this->assertArrayHasKey('contains', $stringOperators);
        $this->assertArrayHasKey('starts_with', $stringOperators);
        $this->assertArrayNotHasKey('greater_than', $stringOperators);
    }

    public function test_discount_condition_get_type_label(): void
    {
        $condition = DiscountCondition::factory()->create(['type' => 'cart_total']);
        
        $this->assertIsString($condition->getTypeLabel());
        $this->assertNotEmpty($condition->getTypeLabel());
    }

    public function test_discount_condition_get_operator_label(): void
    {
        $condition = DiscountCondition::factory()->create(['operator' => 'greater_than']);
        
        $this->assertIsString($condition->getOperatorLabel());
        $this->assertNotEmpty($condition->getOperatorLabel());
    }

    public function test_discount_condition_human_readable_condition(): void
    {
        $condition = DiscountCondition::factory()->create([
            'type' => 'cart_total',
            'operator' => 'greater_than',
            'value' => 100,
        ]);
        
        $this->assertIsString($condition->human_readable_condition);
        $this->assertNotEmpty($condition->human_readable_condition);
    }

    public function test_discount_condition_translated_name(): void
    {
        $condition = DiscountCondition::factory()->create();
        
        $translation = DiscountConditionTranslation::create([
            'discount_condition_id' => $condition->id,
            'locale' => 'lt',
            'name' => 'Test Name',
        ]);
        
        $this->assertEquals('Test Name', $condition->translated_name);
    }

    public function test_discount_condition_translated_description(): void
    {
        $condition = DiscountCondition::factory()->create();
        
        $translation = DiscountConditionTranslation::create([
            'discount_condition_id' => $condition->id,
            'locale' => 'lt',
            'description' => 'Test Description',
        ]);
        
        $this->assertEquals('Test Description', $condition->translated_description);
    }

    public function test_discount_condition_casts(): void
    {
        $condition = DiscountCondition::factory()->create([
            'value' => ['test' => 'data'],
            'metadata' => ['meta' => 'value'],
            'is_active' => true,
            'priority' => 5,
            'position' => 1,
        ]);
        
        $this->assertIsArray($condition->value);
        $this->assertIsArray($condition->metadata);
        $this->assertIsBool($condition->is_active);
        $this->assertIsInt($condition->priority);
        $this->assertIsInt($condition->position);
    }
}