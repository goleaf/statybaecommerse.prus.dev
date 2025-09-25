<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\DiscountConditionResource\Pages\CreateDiscountCondition;
use App\Filament\Resources\DiscountConditionResource\Pages\EditDiscountCondition;
use App\Filament\Resources\DiscountConditionResource\Pages\ListDiscountConditions;
use App\Filament\Resources\DiscountConditionResource\Pages\ViewDiscountCondition;
use App\Models\Category;
use App\Models\Discount;
use App\Models\DiscountCondition;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class DiscountConditionResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($adminUser);
    }

    public function test_can_load_discount_condition_list_page(): void
    {
        $discount = Discount::factory()->create();
        $discountConditions = DiscountCondition::factory()->count(5)->create([
            'discount_id' => $discount->id,
        ]);

        Livewire::test(ListDiscountConditions::class)
            ->assertOk()
            ->assertCanSeeTableRecords($discountConditions);
    }

    public function test_can_create_discount_condition(): void
    {
        $discount = Discount::factory()->create();
        $newDiscountConditionData = DiscountCondition::factory()->make([
            'discount_id' => $discount->id,
            'type' => 'product',
            'operator' => 'equals_to',
            'value' => 'test_value',
        ]);

        Livewire::test(CreateDiscountCondition::class)
            ->fillForm([
                'discount_id' => $newDiscountConditionData->discount_id,
                'type' => $newDiscountConditionData->type,
                'operator' => $newDiscountConditionData->operator,
                'value' => $newDiscountConditionData->value,
                'is_active' => true,
                'priority' => 1,
            ])
            ->call('create')
            ->assertNotified();

        $this->assertDatabaseHas('discount_conditions', [
            'discount_id' => $newDiscountConditionData->discount_id,
            'type' => $newDiscountConditionData->type,
            'operator' => $newDiscountConditionData->operator,
            'is_active' => true,
            'priority' => 1,
        ]);
    }

    public function test_can_edit_discount_condition(): void
    {
        $discount = Discount::factory()->create();
        $discountCondition = DiscountCondition::factory()->create([
            'discount_id' => $discount->id,
            'is_active' => true,
        ]);

        Livewire::test(EditDiscountCondition::class, [
            'record' => $discountCondition->id,
        ])
            ->fillForm([
                'is_active' => false,
                'priority' => 5,
            ])
            ->call('save')
            ->assertNotified();

        $this->assertDatabaseHas('discount_conditions', [
            'id' => $discountCondition->id,
            'is_active' => false,
            'priority' => 5,
        ]);
    }

    public function test_can_view_discount_condition(): void
    {
        $discount = Discount::factory()->create();
        $discountCondition = DiscountCondition::factory()->create([
            'discount_id' => $discount->id,
        ]);

        Livewire::test(ViewDiscountCondition::class, [
            'record' => $discountCondition->id,
        ])
            ->assertOk();
    }

    public function test_can_filter_discount_conditions_by_type(): void
    {
        $discount = Discount::factory()->create();
        $productCondition = DiscountCondition::factory()->create([
            'discount_id' => $discount->id,
            'type' => 'product',
        ]);
        $categoryCondition = DiscountCondition::factory()->create([
            'discount_id' => $discount->id,
            'type' => 'category',
        ]);

        Livewire::test(ListDiscountConditions::class)
            ->filterTable('type', 'product')
            ->assertCanSeeTableRecords([$productCondition])
            ->assertCanNotSeeTableRecords([$categoryCondition]);
    }

    public function test_can_filter_discount_conditions_by_status(): void
    {
        $discount = Discount::factory()->create();
        $activeCondition = DiscountCondition::factory()->create([
            'discount_id' => $discount->id,
            'is_active' => true,
        ]);
        $inactiveCondition = DiscountCondition::factory()->create([
            'discount_id' => $discount->id,
            'is_active' => false,
        ]);

        Livewire::test(ListDiscountConditions::class)
            ->filterTable('is_active', '1')
            ->assertCanSeeTableRecords([$activeCondition])
            ->assertCanNotSeeTableRecords([$inactiveCondition]);
    }

    public function test_can_bulk_activate_discount_conditions(): void
    {
        $discount = Discount::factory()->create();
        $discountConditions = DiscountCondition::factory()->count(3)->create([
            'discount_id' => $discount->id,
            'is_active' => false,
        ]);

        Livewire::test(ListDiscountConditions::class)
            ->callTableBulkAction('activate', $discountConditions);

        foreach ($discountConditions as $condition) {
            $this->assertDatabaseHas('discount_conditions', [
                'id' => $condition->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_can_bulk_deactivate_discount_conditions(): void
    {
        $discount = Discount::factory()->create();
        $discountConditions = DiscountCondition::factory()->count(3)->create([
            'discount_id' => $discount->id,
            'is_active' => true,
        ]);

        Livewire::test(ListDiscountConditions::class)
            ->callTableBulkAction('deactivate', $discountConditions);

        foreach ($discountConditions as $condition) {
            $this->assertDatabaseHas('discount_conditions', [
                'id' => $condition->id,
                'is_active' => false,
            ]);
        }
    }

    public function test_can_toggle_discount_condition_status(): void
    {
        $discount = Discount::factory()->create();
        $discountCondition = DiscountCondition::factory()->create([
            'discount_id' => $discount->id,
            'is_active' => true,
        ]);

        Livewire::test(ListDiscountConditions::class)
            ->callTableAction('toggle_active', $discountCondition);

        $this->assertDatabaseHas('discount_conditions', [
            'id' => $discountCondition->id,
            'is_active' => false,
        ]);
    }

    public function test_discount_condition_form_validation(): void
    {
        $discount = Discount::factory()->create();

        Livewire::test(CreateDiscountCondition::class)
            ->fillForm([
                'discount_id' => $discount->id,
                'type' => '',
                'operator' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['type', 'operator']);
    }

    public function test_discount_condition_relationships(): void
    {
        $discount = Discount::factory()->create();
        $product = Product::factory()->create();
        $category = Category::factory()->create();

        $discountCondition = DiscountCondition::factory()->create([
            'discount_id' => $discount->id,
            'type' => 'product',
        ]);

        $discountCondition->products()->attach($product);
        $discountCondition->categories()->attach($category);

        $this->assertTrue($discountCondition->products->contains($product));
        $this->assertTrue($discountCondition->categories->contains($category));
        $this->assertEquals($discount->id, $discountCondition->discount->id);
    }
}
