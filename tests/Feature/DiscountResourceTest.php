<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Discount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DiscountResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
        ]));
    }

    public function test_can_list_discounts(): void
    {
        $discounts = Discount::factory()->count(3)->create();

        Livewire::test(\App\Filament\Resources\DiscountResource\Pages\ListDiscounts::class)
            ->assertCanSeeTableRecords($discounts);
    }

    public function test_can_create_discount(): void
    {
        $discountData = [
            'name' => 'Test Discount',
            'type' => 'percentage',
            'value' => 10.0,
            'status' => 'active',
            'is_active' => true,
            'is_enabled' => true,
        ];

        Livewire::test(\App\Filament\Resources\DiscountResource\Pages\CreateDiscount::class)
            ->fillForm($discountData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('discounts', [
            'name' => 'Test Discount',
            'type' => 'percentage',
            'value' => 10.0,
        ]);
    }

    public function test_can_edit_discount(): void
    {
        $discount = Discount::factory()->create();

        Livewire::test(\App\Filament\Resources\DiscountResource\Pages\EditDiscount::class, [
            'record' => $discount->id,
        ])
            ->fillForm([
                'name' => 'Updated Discount Name',
                'value' => 15.0,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('discounts', [
            'id' => $discount->id,
            'name' => 'Updated Discount Name',
            'value' => 15.0,
        ]);
    }

    public function test_can_view_discount(): void
    {
        $discount = Discount::factory()->create();

        Livewire::test(\App\Filament\Resources\DiscountResource\Pages\ViewDiscount::class, [
            'record' => $discount->id,
        ])
            ->assertOk();
    }

    public function test_can_delete_discount(): void
    {
        $discount = Discount::factory()->create();

        Livewire::test(\App\Filament\Resources\DiscountResource\Pages\ListDiscounts::class)
            ->callTableAction('delete', $discount)
            ->assertHasNoTableActionErrors();

        $this->assertSoftDeleted('discounts', [
            'id' => $discount->id,
        ]);
    }

    public function test_can_duplicate_discount(): void
    {
        $discount = Discount::factory()->create();

        Livewire::test(\App\Filament\Resources\DiscountResource\Pages\ListDiscounts::class)
            ->callTableAction('duplicate', $discount)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('discounts', [
            'name' => $discount->name.' (Copy)',
            'slug' => $discount->slug.'-copy',
            'status' => 'draft',
        ]);
    }

    public function test_can_filter_discounts_by_type(): void
    {
        $percentageDiscount = Discount::factory()->create(['type' => 'percentage']);
        $fixedDiscount = Discount::factory()->create(['type' => 'fixed']);

        Livewire::test(\App\Filament\Resources\DiscountResource\Pages\ListDiscounts::class)
            ->filterTable('type', 'percentage')
            ->assertCanSeeTableRecords([$percentageDiscount])
            ->assertCanNotSeeTableRecords([$fixedDiscount]);
    }

    public function test_can_filter_discounts_by_status(): void
    {
        $activeDiscount = Discount::factory()->create(['status' => 'active']);
        $draftDiscount = Discount::factory()->create(['status' => 'draft']);

        Livewire::test(\App\Filament\Resources\DiscountResource\Pages\ListDiscounts::class)
            ->filterTable('status', 'active')
            ->assertCanSeeTableRecords([$activeDiscount])
            ->assertCanNotSeeTableRecords([$draftDiscount]);
    }

    public function test_can_bulk_activate_discounts(): void
    {
        $discounts = Discount::factory()->count(3)->create(['is_active' => false]);

        Livewire::test(\App\Filament\Resources\DiscountResource\Pages\ListDiscounts::class)
            ->callTableBulkAction('activate', $discounts)
            ->assertHasNoTableBulkActionErrors();

        foreach ($discounts as $discount) {
            $this->assertDatabaseHas('discounts', [
                'id' => $discount->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_can_bulk_deactivate_discounts(): void
    {
        $discounts = Discount::factory()->count(3)->create(['is_active' => true]);

        Livewire::test(\App\Filament\Resources\DiscountResource\Pages\ListDiscounts::class)
            ->callTableBulkAction('deactivate', $discounts)
            ->assertHasNoTableBulkActionErrors();

        foreach ($discounts as $discount) {
            $this->assertDatabaseHas('discounts', [
                'id' => $discount->id,
                'is_active' => false,
            ]);
        }
    }

    public function test_discount_form_validation(): void
    {
        Livewire::test(\App\Filament\Resources\DiscountResource\Pages\CreateDiscount::class)
            ->fillForm([
                'name' => '',  // Required field
                'type' => 'invalid_type',  // Invalid type
                'value' => -10,  // Negative value
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'type', 'value']);
    }

    public function test_discount_slug_auto_generation(): void
    {
        Livewire::test(\App\Filament\Resources\DiscountResource\Pages\CreateDiscount::class)
            ->fillForm([
                'name' => 'Test Discount Name',
                'type' => 'percentage',
                'value' => 10.0,
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('discounts', [
            'name' => 'Test Discount Name',
            'slug' => 'test-discount-name',
        ]);
    }
}
