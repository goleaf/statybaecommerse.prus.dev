<?php declare(strict_types=1);

use App\Filament\Resources\DiscountResource\Pages\CreateDiscount;
use App\Filament\Resources\DiscountResource\Pages\EditDiscount;
use App\Filament\Resources\DiscountResource\Pages\ListDiscounts;
use App\Filament\Resources\DiscountResource\Pages\ViewDiscount;
use App\Filament\Resources\DiscountResource\Widgets\DiscountChartWidget;
use App\Filament\Resources\DiscountResource\Widgets\DiscountStatsWidget;
use App\Filament\Resources\DiscountResource\Widgets\RecentRedemptionsWidget;
use App\Filament\Resources\DiscountResource;
use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\DiscountCondition;
use App\Models\DiscountRedemption;
use App\Models\User;
use Livewire\Livewire;

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

it('can view discount details', function () {
    $discount = Discount::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(ViewDiscount::class, ['record' => $discount->id])
        ->assertOk();
});

it('can duplicate a discount', function () {
    $discount = Discount::factory()->create(['name' => 'Original Discount']);

    Livewire::actingAs($this->adminUser)
        ->test(ListDiscounts::class)
        ->callTableAction('duplicate', $discount)
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('discounts', [
        'name' => 'Original Discount (Copy)',
        'slug' => $discount->slug . '-copy',
        'status' => 'draft',
        'usage_count' => 0,
    ]);
});

it('can filter discounts by status', function () {
    $activeDiscount = Discount::factory()->create(['status' => 'active']);
    $draftDiscount = Discount::factory()->create(['status' => 'draft']);

    Livewire::actingAs($this->adminUser)
        ->test(ListDiscounts::class)
        ->filterTable('status', 'active')
        ->assertCanSeeTableRecords([$activeDiscount])
        ->assertCanNotSeeTableRecords([$draftDiscount]);
});

it('can filter expired discounts', function () {
    $expiredDiscount = Discount::factory()->create(['ends_at' => now()->subDay()]);
    $activeDiscount = Discount::factory()->create(['ends_at' => now()->addDay()]);

    Livewire::actingAs($this->adminUser)
        ->test(ListDiscounts::class)
        ->filterTable('expired')
        ->assertCanSeeTableRecords([$expiredDiscount])
        ->assertCanNotSeeTableRecords([$activeDiscount]);
});

it('can filter scheduled discounts', function () {
    $scheduledDiscount = Discount::factory()->create(['starts_at' => now()->addDay()]);
    $activeDiscount = Discount::factory()->create(['starts_at' => now()->subDay()]);

    Livewire::actingAs($this->adminUser)
        ->test(ListDiscounts::class)
        ->filterTable('scheduled')
        ->assertCanSeeTableRecords([$scheduledDiscount])
        ->assertCanNotSeeTableRecords([$activeDiscount]);
});

it('can filter exclusive discounts', function () {
    $exclusiveDiscount = Discount::factory()->create(['exclusive' => true]);
    $regularDiscount = Discount::factory()->create(['exclusive' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(ListDiscounts::class)
        ->filterTable('exclusive')
        ->assertCanSeeTableRecords([$exclusiveDiscount])
        ->assertCanNotSeeTableRecords([$regularDiscount]);
});

it('can bulk activate discounts', function () {
    $discount1 = Discount::factory()->create(['is_active' => false]);
    $discount2 = Discount::factory()->create(['is_active' => false]);

    Livewire::actingAs($this->adminUser)
        ->test(ListDiscounts::class)
        ->callTableBulkAction('activate', [$discount1->id, $discount2->id])
        ->assertOk();

    expect($discount1->fresh()->is_active)->toBeTrue();
    expect($discount2->fresh()->is_active)->toBeTrue();
});

it('can bulk deactivate discounts', function () {
    $discount1 = Discount::factory()->create(['is_active' => true]);
    $discount2 = Discount::factory()->create(['is_active' => true]);

    Livewire::actingAs($this->adminUser)
        ->test(ListDiscounts::class)
        ->callTableBulkAction('deactivate', [$discount1->id, $discount2->id])
        ->assertOk();

    expect($discount1->fresh()->is_active)->toBeFalse();
    expect($discount2->fresh()->is_active)->toBeFalse();
});

it('shows discount stats widget', function () {
    Discount::factory()->count(5)->create();

    Livewire::actingAs($this->adminUser)
        ->test(DiscountStatsWidget::class)
        ->assertOk();
});

it('shows discount chart widget', function () {
    Discount::factory()->count(3)->create();

    Livewire::actingAs($this->adminUser)
        ->test(DiscountChartWidget::class)
        ->assertOk();
});

it('shows recent redemptions widget', function () {
    $discount = Discount::factory()->create();
    DiscountRedemption::factory()->count(3)->create(['discount_id' => $discount->id]);

    Livewire::actingAs($this->adminUser)
        ->test(RecentRedemptionsWidget::class)
        ->assertOk();
});

it('validates discount value for percentage type', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CreateDiscount::class)
        ->fillForm([
            'name' => 'Test Discount',
            'type' => 'percentage',
            'value' => 150,  // Invalid percentage
        ])
        ->call('create')
        ->assertHasFormErrors(['value']);
});

it('validates discount value for fixed type', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CreateDiscount::class)
        ->fillForm([
            'name' => 'Test Discount',
            'type' => 'fixed',
            'value' => -10,  // Invalid negative value
        ])
        ->call('create')
        ->assertHasFormErrors(['value']);
});

it('allows free shipping discount without value', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CreateDiscount::class)
        ->fillForm([
            'name' => 'Free Shipping',
            'type' => 'free_shipping',
            'value' => null,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('discounts', [
        'name' => 'Free Shipping',
        'type' => 'free_shipping',
        'value' => null,
    ]);
});
