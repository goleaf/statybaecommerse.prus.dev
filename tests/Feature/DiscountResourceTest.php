<?php declare(strict_types=1);

use App\Models\Discount;
use App\Models\User;
use Livewire\Livewire;
use App\Filament\Resources\DiscountResource;
use App\Filament\Resources\DiscountResource\Pages\ListDiscounts;
use App\Filament\Resources\DiscountResource\Pages\CreateDiscount;
use App\Filament\Resources\DiscountResource\Pages\ViewDiscount;
use App\Filament\Resources\DiscountResource\Pages\EditDiscount;

beforeEach(function () {
    $this->adminUser = User::factory()->create(['is_admin' => true]);
});

it('can list discounts in admin panel', function () {
    $discount = Discount::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(ListDiscounts::class)
        ->assertCanSeeTableRecords([$discount]);
});

it('can create a new discount', function () {
    $discountData = [
        'name' => 'Test Discount',
        'type' => 'percentage',
        'value' => 10,
        'is_active' => true,
    ];
    
    Livewire::actingAs($this->adminUser)
        ->test(CreateDiscount::class)
        ->fillForm($discountData)
        ->call('create')
        ->assertHasNoFormErrors();
        
    $this->assertDatabaseHas('discounts', [
        'name' => 'Test Discount',
        'type' => 'percentage',
        'value' => 10,
        'is_active' => true,
    ]);
});

it('can view a discount', function () {
    $discount = Discount::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(ViewDiscount::class, ['record' => $discount->id])
        ->assertOk();
});

it('can edit a discount', function () {
    $discount = Discount::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(EditDiscount::class, ['record' => $discount->id])
        ->fillForm([
            'name' => 'Updated Discount',
            'value' => 15,
        ])
        ->call('save')
        ->assertHasNoFormErrors();
        
    $this->assertDatabaseHas('discounts', [
        'id' => $discount->id,
        'name' => 'Updated Discount',
        'value' => 15,
    ]);
});

it('can delete a discount', function () {
    $discount = Discount::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(ListDiscounts::class)
        ->callTableAction('delete', $discount)
        ->assertHasNoTableActionErrors();
    
    $this->assertSoftDeleted('discounts', [
        'id' => $discount->id,
    ]);
});

it('validates required fields when creating discount', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CreateDiscount::class)
        ->fillForm([
            'name' => null,
            'type' => null,
            'value' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name', 'type', 'value']);
});

it('validates discount type options', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CreateDiscount::class)
        ->fillForm([
            'name' => 'Test Discount',
            'type' => 'invalid_type',
            'value' => 10,
        ])
        ->call('create')
        ->assertHasFormErrors(['type']);
});

it('validates numeric value field', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CreateDiscount::class)
        ->fillForm([
            'name' => 'Test Discount',
            'type' => 'percentage',
            'value' => 'not_a_number',
        ])
        ->call('create')
        ->assertHasFormErrors(['value']);
});

it('can filter discounts by type', function () {
    $percentageDiscount = Discount::factory()->create(['type' => 'percentage']);
    $fixedDiscount = Discount::factory()->create(['type' => 'fixed']);
    
    Livewire::actingAs($this->adminUser)
        ->test(ListDiscounts::class)
        ->filterTable('type', 'percentage')
        ->assertCanSeeTableRecords([$percentageDiscount])
        ->assertCanNotSeeTableRecords([$fixedDiscount]);
});

it('can filter discounts by active status', function () {
    $activeDiscount = Discount::factory()->create(['is_active' => true]);
    $inactiveDiscount = Discount::factory()->create(['is_active' => false]);
    
    Livewire::actingAs($this->adminUser)
        ->test(ListDiscounts::class)
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords([$activeDiscount])
        ->assertCanNotSeeTableRecords([$inactiveDiscount]);
});

it('shows correct discount data in table', function () {
    $discount = Discount::factory()->create([
        'name' => 'Test Discount',
        'type' => 'percentage',
        'value' => 10,
        'is_active' => true,
    ]);
    
    Livewire::actingAs($this->adminUser)
        ->test(ListDiscounts::class)
        ->assertCanSeeTableRecords([$discount])
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('type')
        ->assertCanRenderTableColumn('value')
        ->assertCanRenderTableColumn('is_active');
});

it('handles discount activation and deactivation', function () {
    $discount = Discount::factory()->create(['is_active' => false]);
    
    Livewire::actingAs($this->adminUser)
        ->test(EditDiscount::class, ['record' => $discount->id])
        ->fillForm(['is_active' => true])
        ->call('save')
        ->assertHasNoFormErrors();
        
    expect($discount->fresh()->is_active)->toBeTrue();
});

it('can set usage limits', function () {
    $discount = Discount::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(EditDiscount::class, ['record' => $discount->id])
        ->fillForm([
            'usage_limit' => 100,
        ])
        ->call('save')
        ->assertHasNoFormErrors();
        
    $this->assertDatabaseHas('discounts', [
        'id' => $discount->id,
        'usage_limit' => 100,
    ]);
});

it('can set start and expiration dates', function () {
    $discount = Discount::factory()->create();
    $startsAt = now()->addDay();
    $expiresAt = now()->addMonth();
    
    Livewire::actingAs($this->adminUser)
        ->test(EditDiscount::class, ['record' => $discount->id])
        ->fillForm([
            'starts_at' => $startsAt,
            'ends_at' => $expiresAt,
        ])
        ->call('save')
        ->assertHasNoFormErrors();
        
    $this->assertDatabaseHas('discounts', [
        'id' => $discount->id,
        'starts_at' => $startsAt->format('Y-m-d H:i:s'),
        'ends_at' => $expiresAt->format('Y-m-d H:i:s'),
    ]);
});

it('can search discounts by name', function () {
    $discount1 = Discount::factory()->create(['name' => 'Summer Sale']);
    $discount2 = Discount::factory()->create(['name' => 'Winter Discount']);
    
    Livewire::actingAs($this->adminUser)
        ->test(ListDiscounts::class)
        ->searchTable('Summer')
        ->assertCanSeeTableRecords([$discount1])
        ->assertCanNotSeeTableRecords([$discount2]);
});

it('can search discounts by slug', function () {
    $discount1 = Discount::factory()->create(['slug' => 'SUMMER2024']);
    $discount2 = Discount::factory()->create(['slug' => 'WINTER2024']);
    
    Livewire::actingAs($this->adminUser)
        ->test(ListDiscounts::class)
        ->searchTable('SUMMER')
        ->assertCanSeeTableRecords([$discount1])
        ->assertCanNotSeeTableRecords([$discount2]);
});

it('handles bulk actions on discounts', function () {
    $discount1 = Discount::factory()->create();
    $discount2 = Discount::factory()->create();
    
    Livewire::actingAs($this->adminUser)
        ->test(ListDiscounts::class)
        ->callTableBulkAction('delete', [$discount1->id, $discount2->id])
        ->assertOk();
    
    $this->assertSoftDeleted('discounts', [
        'id' => $discount1->id,
    ]);
    
    $this->assertSoftDeleted('discounts', [
        'id' => $discount2->id,
    ]);
});

it('validates unique discount slug', function () {
    $existingDiscount = Discount::factory()->create(['slug' => 'UNIQUE']);
    
    Livewire::actingAs($this->adminUser)
        ->test(CreateDiscount::class)
        ->fillForm([
            'name' => 'Another Discount',
            'type' => 'percentage',
            'value' => 10,
            'slug' => 'UNIQUE',
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

it('can create discount without slug', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CreateDiscount::class)
        ->fillForm([
            'name' => 'Test Discount',
            'type' => 'percentage',
            'value' => 10,
            'slug' => null,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
        
    $this->assertDatabaseHas('discounts', [
        'name' => 'Test Discount',
        'slug' => null,
    ]);
});
