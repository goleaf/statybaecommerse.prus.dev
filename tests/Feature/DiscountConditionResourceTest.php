<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Discount;
use App\Models\DiscountCondition;
use App\Models\Translations\DiscountConditionTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class DiscountConditionResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'is_admin' => true,
        ]);
    }

    public function test_can_list_discount_conditions(): void
    {
        $discount = Discount::factory()->create();
        $condition = DiscountCondition::factory()->create(['discount_id' => $discount->id]);

        $this->actingAs($this->adminUser)
            ->get('/admin/discount-conditions')
            ->assertOk()
            ->assertSee($condition->type)
            ->assertSee($condition->operator);
    }

    public function test_can_create_discount_condition(): void
    {
        $discount = Discount::factory()->create();

        $this->actingAs($this->adminUser)
            ->get('/admin/discount-conditions/create')
            ->assertOk();

        $this->actingAs($this->adminUser)
            ->post('/admin/discount-conditions', [
                'discount_id' => $discount->id,
                'type' => 'cart_total',
                'operator' => 'greater_than',
                'value' => 100,
                'position' => 1,
                'is_active' => true,
                'priority' => 5,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('discount_conditions', [
            'discount_id' => $discount->id,
            'type' => 'cart_total',
            'operator' => 'greater_than',
            'value' => json_encode(100),
            'position' => 1,
            'is_active' => true,
            'priority' => 5,
        ]);
    }

    public function test_can_view_discount_condition(): void
    {
        $discount = Discount::factory()->create();
        $condition = DiscountCondition::factory()->create(['discount_id' => $discount->id]);

        $this->actingAs($this->adminUser)
            ->get("/admin/discount-conditions/{$condition->id}")
            ->assertOk()
            ->assertSee($condition->type)
            ->assertSee($condition->operator);
    }

    public function test_can_edit_discount_condition(): void
    {
        $discount = Discount::factory()->create();
        $condition = DiscountCondition::factory()->create(['discount_id' => $discount->id]);

        $this->actingAs($this->adminUser)
            ->get("/admin/discount-conditions/{$condition->id}/edit")
            ->assertOk();

        $this->actingAs($this->adminUser)
            ->put("/admin/discount-conditions/{$condition->id}", [
                'discount_id' => $discount->id,
                'type' => 'product',
                'operator' => 'contains',
                'value' => 'test',
                'position' => 2,
                'is_active' => false,
                'priority' => 10,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('discount_conditions', [
            'id' => $condition->id,
            'type' => 'product',
            'operator' => 'contains',
            'value' => json_encode('test'),
            'position' => 2,
            'is_active' => false,
            'priority' => 10,
        ]);
    }

    public function test_can_delete_discount_condition(): void
    {
        $discount = Discount::factory()->create();
        $condition = DiscountCondition::factory()->create(['discount_id' => $discount->id]);

        $this->actingAs($this->adminUser)
            ->delete("/admin/discount-conditions/{$condition->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('discount_conditions', [
            'id' => $condition->id,
        ]);
    }

    public function test_can_filter_discount_conditions_by_type(): void
    {
        DiscountCondition::factory()->create(['type' => 'cart_total']);
        DiscountCondition::factory()->create(['type' => 'product']);

        $this->actingAs($this->adminUser)
            ->get('/admin/discount-conditions?type=cart_total')
            ->assertOk()
            ->assertSee('cart_total')
            ->assertDontSee('product');
    }

    public function test_can_filter_discount_conditions_by_discount(): void
    {
        $discount1 = Discount::factory()->create();
        $discount2 = Discount::factory()->create();
        
        DiscountCondition::factory()->create(['discount_id' => $discount1->id]);
        DiscountCondition::factory()->create(['discount_id' => $discount2->id]);

        $this->actingAs($this->adminUser)
            ->get("/admin/discount-conditions?discount_id={$discount1->id}")
            ->assertOk();
    }

    public function test_can_filter_discount_conditions_by_status(): void
    {
        DiscountCondition::factory()->create(['is_active' => true]);
        DiscountCondition::factory()->create(['is_active' => false]);

        $this->actingAs($this->adminUser)
            ->get('/admin/discount-conditions?is_active=1')
            ->assertOk();
    }

    public function test_can_bulk_activate_conditions(): void
    {
        $condition1 = DiscountCondition::factory()->create(['is_active' => false]);
        $condition2 = DiscountCondition::factory()->create(['is_active' => false]);

        $this->actingAs($this->adminUser)
            ->post('/admin/discount-conditions/bulk-activate', [
                'selectedItems' => [$condition1->id, $condition2->id],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('discount_conditions', [
            'id' => $condition1->id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('discount_conditions', [
            'id' => $condition2->id,
            'is_active' => true,
        ]);
    }

    public function test_can_bulk_deactivate_conditions(): void
    {
        $condition1 = DiscountCondition::factory()->create(['is_active' => true]);
        $condition2 = DiscountCondition::factory()->create(['is_active' => true]);

        $this->actingAs($this->adminUser)
            ->post('/admin/discount-conditions/bulk-deactivate', [
                'selectedItems' => [$condition1->id, $condition2->id],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('discount_conditions', [
            'id' => $condition1->id,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('discount_conditions', [
            'id' => $condition2->id,
            'is_active' => false,
        ]);
    }

    public function test_can_bulk_set_priority(): void
    {
        $condition1 = DiscountCondition::factory()->create(['priority' => 1]);
        $condition2 = DiscountCondition::factory()->create(['priority' => 2]);

        $this->actingAs($this->adminUser)
            ->post('/admin/discount-conditions/bulk-set-priority', [
                'selectedItems' => [$condition1->id, $condition2->id],
                'priority' => 10,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('discount_conditions', [
            'id' => $condition1->id,
            'priority' => 10,
        ]);

        $this->assertDatabaseHas('discount_conditions', [
            'id' => $condition2->id,
            'priority' => 10,
        ]);
    }

    public function test_can_test_condition(): void
    {
        $condition = DiscountCondition::factory()->create([
            'type' => 'cart_total',
            'operator' => 'greater_than',
            'value' => 100,
            'is_active' => true,
        ]);

        $this->actingAs($this->adminUser)
            ->post("/admin/discount-conditions/{$condition->id}/test", [
                'test_value' => 150,
            ])
            ->assertOk()
            ->assertJson([
                'matches' => true,
                'is_valid' => true,
            ]);
    }

    public function test_can_create_condition_with_translations(): void
    {
        $discount = Discount::factory()->create();

        $this->actingAs($this->adminUser)
            ->post('/admin/discount-conditions', [
                'discount_id' => $discount->id,
                'type' => 'cart_total',
                'operator' => 'greater_than',
                'value' => 100,
                'position' => 1,
                'is_active' => true,
                'priority' => 5,
                'translations' => [
                    [
                        'locale' => 'lt',
                        'name' => 'Krepšelio suma',
                        'description' => 'Sąlyga krepšelio sumai',
                    ],
                    [
                        'locale' => 'en',
                        'name' => 'Cart Total',
                        'description' => 'Condition for cart total',
                    ],
                ],
            ])
            ->assertRedirect();

        $condition = DiscountCondition::latest()->first();
        
        $this->assertDatabaseHas('discount_condition_translations', [
            'discount_condition_id' => $condition->id,
            'locale' => 'lt',
            'name' => 'Krepšelio suma',
        ]);

        $this->assertDatabaseHas('discount_condition_translations', [
            'discount_condition_id' => $condition->id,
            'locale' => 'en',
            'name' => 'Cart Total',
        ]);
    }

    public function test_can_edit_condition_with_translations(): void
    {
        $discount = Discount::factory()->create();
        $condition = DiscountCondition::factory()->create(['discount_id' => $discount->id]);
        
        DiscountConditionTranslation::factory()->create([
            'discount_condition_id' => $condition->id,
            'locale' => 'lt',
            'name' => 'Old Name',
        ]);

        $this->actingAs($this->adminUser)
            ->put("/admin/discount-conditions/{$condition->id}", [
                'discount_id' => $discount->id,
                'type' => 'cart_total',
                'operator' => 'greater_than',
                'value' => 100,
                'position' => 1,
                'is_active' => true,
                'priority' => 5,
                'translations' => [
                    [
                        'locale' => 'lt',
                        'name' => 'New Name',
                        'description' => 'New Description',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('discount_condition_translations', [
            'discount_condition_id' => $condition->id,
            'locale' => 'lt',
            'name' => 'New Name',
            'description' => 'New Description',
        ]);
    }

    public function test_validation_requires_discount_id(): void
    {
        $this->actingAs($this->adminUser)
            ->post('/admin/discount-conditions', [
                'type' => 'cart_total',
                'operator' => 'greater_than',
                'value' => 100,
            ])
            ->assertSessionHasErrors(['discount_id']);
    }

    public function test_validation_requires_type(): void
    {
        $discount = Discount::factory()->create();

        $this->actingAs($this->adminUser)
            ->post('/admin/discount-conditions', [
                'discount_id' => $discount->id,
                'operator' => 'greater_than',
                'value' => 100,
            ])
            ->assertSessionHasErrors(['type']);
    }

    public function test_validation_requires_operator(): void
    {
        $discount = Discount::factory()->create();

        $this->actingAs($this->adminUser)
            ->post('/admin/discount-conditions', [
                'discount_id' => $discount->id,
                'type' => 'cart_total',
                'value' => 100,
            ])
            ->assertSessionHasErrors(['operator']);
    }

    public function test_validation_requires_value(): void
    {
        $discount = Discount::factory()->create();

        $this->actingAs($this->adminUser)
            ->post('/admin/discount-conditions', [
                'discount_id' => $discount->id,
                'type' => 'cart_total',
                'operator' => 'greater_than',
            ])
            ->assertSessionHasErrors(['value']);
    }
}
