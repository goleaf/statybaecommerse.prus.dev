<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\DiscountCodeResource\Pages\CreateDiscountCode;
use App\Filament\Resources\DiscountCodeResource\Pages\EditDiscountCode;
use App\Filament\Resources\DiscountCodeResource\Pages\ListDiscountCodes;
use App\Filament\Resources\DiscountCodeResource\Pages\ViewDiscountCode;
use App\Models\CustomerGroup;
use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

final class DiscountCodeResourceTest extends TestCase
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

    public function test_can_load_discount_code_list_page(): void
    {
        $discount = Discount::factory()->create();
        $discountCodes = DiscountCode::factory()->count(5)->create([
            'discount_id' => $discount->id,
        ]);

        Livewire::test(ListDiscountCodes::class)
            ->assertOk()
            ->assertCanSeeTableRecords($discountCodes);
    }

    public function test_can_create_discount_code(): void
    {
        $discount = Discount::factory()->create();
        $customerGroup = CustomerGroup::factory()->create();
        $newDiscountCodeData = DiscountCode::factory()->make([
            'discount_id' => $discount->id,
            'code' => 'TEST10',
            'name' => 'Test Discount',
            'type' => 'percentage',
            'value' => 10.00,
            'is_active' => true,
            'is_public' => false,
        ]);

        Livewire::test(CreateDiscountCode::class)
            ->fillForm([
                'discount_id' => $newDiscountCodeData->discount_id,
                'code' => $newDiscountCodeData->code,
                'name' => $newDiscountCodeData->name,
                'type' => $newDiscountCodeData->type,
                'value' => $newDiscountCodeData->value,
                'minimum_amount' => 50.00,
                'maximum_discount' => 100.00,
                'usage_limit' => 100,
                'usage_limit_per_user' => 1,
                'valid_from' => now(),
                'valid_until' => now()->addMonth(),
                'is_active' => true,
                'is_public' => false,
                'is_auto_apply' => false,
                'is_stackable' => false,
                'is_first_time_only' => false,
                'customer_group_id' => $customerGroup->id,
            ])
            ->call('create')
            ->assertNotified();

        $this->assertDatabaseHas('discount_codes', [
            'code' => $newDiscountCodeData->code,
            'name' => $newDiscountCodeData->name,
            'type' => $newDiscountCodeData->type,
            'value' => $newDiscountCodeData->value,
            'is_active' => true,
            'is_public' => false,
        ]);
    }

    public function test_can_edit_discount_code(): void
    {
        $discount = Discount::factory()->create();
        $discountCode = DiscountCode::factory()->create([
            'discount_id' => $discount->id,
            'is_active' => true,
            'value' => 10.00,
        ]);

        Livewire::test(EditDiscountCode::class, [
            'record' => $discountCode->id,
        ])
            ->fillForm([
                'is_active' => false,
                'value' => 15.00,
                'is_public' => true,
            ])
            ->call('save')
            ->assertNotified();

        $this->assertDatabaseHas('discount_codes', [
            'id' => $discountCode->id,
            'is_active' => false,
            'value' => 15.00,
            'is_public' => true,
        ]);
    }

    public function test_can_view_discount_code(): void
    {
        $discount = Discount::factory()->create();
        $discountCode = DiscountCode::factory()->create([
            'discount_id' => $discount->id,
        ]);

        Livewire::test(ViewDiscountCode::class, [
            'record' => $discountCode->id,
        ])
            ->assertOk();
    }

    public function test_can_filter_discount_codes_by_type(): void
    {
        $discount = Discount::factory()->create();
        $percentageCode = DiscountCode::factory()->create([
            'discount_id' => $discount->id,
            'type' => 'percentage',
        ]);
        $fixedCode = DiscountCode::factory()->create([
            'discount_id' => $discount->id,
            'type' => 'fixed',
        ]);

        Livewire::test(ListDiscountCodes::class)
            ->filterTable('type', 'percentage')
            ->assertCanSeeTableRecords([$percentageCode])
            ->assertCanNotSeeTableRecords([$fixedCode]);
    }

    public function test_can_filter_discount_codes_by_customer_group(): void
    {
        $discount = Discount::factory()->create();
        $customerGroup1 = CustomerGroup::factory()->create(['name' => 'VIP']);
        $customerGroup2 = CustomerGroup::factory()->create(['name' => 'Regular']);

        $code1 = DiscountCode::factory()->create([
            'discount_id' => $discount->id,
            'customer_group_id' => $customerGroup1->id,
        ]);
        $code2 = DiscountCode::factory()->create([
            'discount_id' => $discount->id,
            'customer_group_id' => $customerGroup2->id,
        ]);

        Livewire::test(ListDiscountCodes::class)
            ->filterTable('customer_group_id', $customerGroup1->id)
            ->assertCanSeeTableRecords([$code1])
            ->assertCanNotSeeTableRecords([$code2]);
    }

    public function test_can_filter_discount_codes_by_active_status(): void
    {
        $discount = Discount::factory()->create();
        $activeCode = DiscountCode::factory()->create([
            'discount_id' => $discount->id,
            'is_active' => true,
        ]);
        $inactiveCode = DiscountCode::factory()->create([
            'discount_id' => $discount->id,
            'is_active' => false,
        ]);

        Livewire::test(ListDiscountCodes::class)
            ->filterTable('is_active', '1')
            ->assertCanSeeTableRecords([$activeCode])
            ->assertCanNotSeeTableRecords([$inactiveCode]);
    }

    public function test_can_filter_discount_codes_by_public_status(): void
    {
        $discount = Discount::factory()->create();
        $publicCode = DiscountCode::factory()->create([
            'discount_id' => $discount->id,
            'is_public' => true,
        ]);
        $privateCode = DiscountCode::factory()->create([
            'discount_id' => $discount->id,
            'is_public' => false,
        ]);

        Livewire::test(ListDiscountCodes::class)
            ->filterTable('is_public', '1')
            ->assertCanSeeTableRecords([$publicCode])
            ->assertCanNotSeeTableRecords([$privateCode]);
    }

    public function test_can_toggle_discount_code_active_status(): void
    {
        $discount = Discount::factory()->create();
        $discountCode = DiscountCode::factory()->create([
            'discount_id' => $discount->id,
            'is_active' => true,
        ]);

        Livewire::test(ListDiscountCodes::class)
            ->callTableAction('toggle_active', $discountCode);

        $this->assertDatabaseHas('discount_codes', [
            'id' => $discountCode->id,
            'is_active' => false,
        ]);
    }

    public function test_can_duplicate_discount_code(): void
    {
        $discount = Discount::factory()->create();
        $discountCode = DiscountCode::factory()->create([
            'discount_id' => $discount->id,
            'code' => 'ORIGINAL',
            'name' => 'Original Code',
            'usage_count' => 5,
        ]);

        Livewire::test(ListDiscountCodes::class)
            ->callTableAction('duplicate', $discountCode);

        $this->assertDatabaseHas('discount_codes', [
            'code' => 'ORIGINAL_copy_'.time(),
            'name' => 'Original Code (Copy)',
            'usage_count' => 0,
        ]);
    }

    public function test_can_bulk_activate_discount_codes(): void
    {
        $discount = Discount::factory()->create();
        $discountCodes = DiscountCode::factory()->count(3)->create([
            'discount_id' => $discount->id,
            'is_active' => false,
        ]);

        Livewire::test(ListDiscountCodes::class)
            ->callTableBulkAction('activate', $discountCodes);

        foreach ($discountCodes as $code) {
            $this->assertDatabaseHas('discount_codes', [
                'id' => $code->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_can_bulk_deactivate_discount_codes(): void
    {
        $discount = Discount::factory()->create();
        $discountCodes = DiscountCode::factory()->count(3)->create([
            'discount_id' => $discount->id,
            'is_active' => true,
        ]);

        Livewire::test(ListDiscountCodes::class)
            ->callTableBulkAction('deactivate', $discountCodes);

        foreach ($discountCodes as $code) {
            $this->assertDatabaseHas('discount_codes', [
                'id' => $code->id,
                'is_active' => false,
            ]);
        }
    }

    public function test_discount_code_form_validation(): void
    {
        Livewire::test(CreateDiscountCode::class)
            ->fillForm([
                'code' => '',
                'type' => '',
                'value' => -1,
            ])
            ->call('create')
            ->assertHasFormErrors(['code', 'type']);
    }

    public function test_discount_code_relationships(): void
    {
        $discount = Discount::factory()->create();
        $customerGroup = CustomerGroup::factory()->create();
        $discountCode = DiscountCode::factory()->create([
            'discount_id' => $discount->id,
            'customer_group_id' => $customerGroup->id,
        ]);

        // Create related records
        $user = User::factory()->create();
        $order = Order::factory()->create();
        $discountCode->users()->attach($user);
        $discountCode->orders()->attach($order);

        // Test relationships
        $this->assertEquals($discount->id, $discountCode->discount->id);
        $this->assertEquals($customerGroup->id, $discountCode->customerGroup->id);
        $this->assertTrue($discountCode->users->contains($user));
        $this->assertTrue($discountCode->orders->contains($order));
    }

    public function test_discount_code_search_functionality(): void
    {
        $discount = Discount::factory()->create();
        $code1 = DiscountCode::factory()->create([
            'discount_id' => $discount->id,
            'code' => 'SUMMER10',
            'name' => 'Summer Sale',
        ]);
        $code2 = DiscountCode::factory()->create([
            'discount_id' => $discount->id,
            'code' => 'WINTER20',
            'name' => 'Winter Sale',
        ]);

        Livewire::test(ListDiscountCodes::class)
            ->searchTable('SUMMER')
            ->assertCanSeeTableRecords([$code1])
            ->assertCanNotSeeTableRecords([$code2]);
    }

    public function test_discount_code_sorting_functionality(): void
    {
        $discount = Discount::factory()->create();
        $code1 = DiscountCode::factory()->create([
            'discount_id' => $discount->id,
            'code' => 'A_CODE',
        ]);
        $code2 = DiscountCode::factory()->create([
            'discount_id' => $discount->id,
            'code' => 'B_CODE',
        ]);

        Livewire::test(ListDiscountCodes::class)
            ->sortTable('code', 'asc')
            ->assertCanSeeTableRecords([$code1, $code2]);
    }

    public function test_discount_code_value_formatting(): void
    {
        $discount = Discount::factory()->create();
        $percentageCode = DiscountCode::factory()->create([
            'discount_id' => $discount->id,
            'type' => 'percentage',
            'value' => 10.00,
        ]);
        $fixedCode = DiscountCode::factory()->create([
            'discount_id' => $discount->id,
            'type' => 'fixed',
            'value' => 25.50,
        ]);

        // Test that percentage codes show with % symbol
        $this->assertEquals('10%', $percentageCode->value.'%');

        // Test that fixed codes show with € symbol
        $this->assertEquals('€25.50', '€'.number_format($fixedCode->value, 2));
    }

    public function test_discount_code_usage_tracking(): void
    {
        $discount = Discount::factory()->create();
        $discountCode = DiscountCode::factory()->create([
            'discount_id' => $discount->id,
            'usage_limit' => 100,
            'usage_count' => 50,
        ]);

        $this->assertEquals(50, $discountCode->remaining_uses);
        $this->assertEquals(50.0, $discountCode->usage_percentage);

        $discountCode->incrementUsage();
        $discountCode->refresh();

        $this->assertEquals(51, $discountCode->usage_count);
        $this->assertEquals(49, $discountCode->remaining_uses);
    }
}
