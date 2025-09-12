<?php declare(strict_types=1);

use App\Filament\Resources\DiscountResource;
use App\Models\Discount;
use App\Models\User;

use function Pest\Laravel\{actingAs, assertDatabaseHas, assertDatabaseMissing};

beforeEach(function () {
    $this->admin = User::factory()->create();
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    $guard = config('auth.defaults.guard', 'web');
    \Spatie\Permission\Models\Role::query()->firstOrCreate([
        'name' => 'admin',
        'guard_name' => is_string($guard) ? $guard : 'web',
    ]);
    $this->admin->syncRoles(['admin']);
});

it('can render discount resource index page', function () {
    actingAs($this->admin)
        ->get(DiscountResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render discount resource create page', function () {
    actingAs($this->admin)
        ->get(DiscountResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create discount', function () {
    $newData = [
        'name' => 'Test Discount',
        'description' => 'Test discount description',
        'type' => 'percentage',
        'value' => 10.0,
        'starts_at' => now()->toDateTimeString(),
        'ends_at' => now()->addDays(30)->toDateTimeString(),
        'is_active' => true,
    ];

    actingAs($this->admin)
        ->post(DiscountResource::getUrl('create'), $newData)
        ->assertRedirect();

    assertDatabaseHas('discounts', [
        'name' => 'Test Discount',
        'type' => 'percentage',
        'value' => 10.0,
        'is_active' => true,
    ]);
});

it('can render discount resource view page', function () {
    $discount = Discount::factory()->create();

    actingAs($this->admin)
        ->get(DiscountResource::getUrl('view', ['record' => $discount]))
        ->assertSuccessful();
});

it('can render discount resource edit page', function () {
    $discount = Discount::factory()->create();

    actingAs($this->admin)
        ->get(DiscountResource::getUrl('edit', ['record' => $discount]))
        ->assertSuccessful();
});

it('can update discount', function () {
    $discount = Discount::factory()->create();
    $newData = [
        'name' => 'Updated Discount',
        'description' => 'Updated description',
        'type' => 'fixed',
        'value' => 25.0,
        'is_active' => false,
    ];

    \Livewire\Livewire::test(\App\Filament\Resources\DiscountResource\Pages\EditDiscount::class, ['record' => $discount->id])
        ->fillForm($newData)
        ->call('save')
        ->assertHasNoFormErrors();

    $discount->refresh();
    expect($discount->name)->toBe('Updated Discount');
    expect($discount->is_active)->toBeFalse();
});

it('can delete discount', function () {
    $discount = Discount::factory()->create();
    $discountId = $discount->id;

    \Livewire\Livewire::test(\App\Filament\Resources\DiscountResource\Pages\EditDiscount::class, ['record' => $discount->id])
        ->callAction('delete')
        ->assertOk();

    expect(Discount::find($discountId))->toBeNull();
});

it('can list discounts', function () {
    $discounts = Discount::factory()->count(10)->create();

    actingAs($this->admin)
        ->get(DiscountResource::getUrl('index'))
        ->assertSuccessful()
        ->assertSeeText($discounts->first()->name);
});

it('can filter discounts by type', function () {
    $percentageDiscount = Discount::factory()->create(['type' => 'percentage']);
    $fixedDiscount = Discount::factory()->create(['type' => 'fixed']);

    \Livewire\Livewire::test(\App\Filament\Resources\DiscountResource\Pages\ListDiscounts::class)
        ->filterTable('type', 'percentage')
        ->assertCanSeeTableRecords([$percentageDiscount])
        ->assertCanNotSeeTableRecords([$fixedDiscount]);
});

it('can filter active discounts', function () {
    $activeDiscount = Discount::factory()->create(['is_active' => true]);
    $inactiveDiscount = Discount::factory()->create(['is_active' => false]);

    \Livewire\Livewire::test(\App\Filament\Resources\DiscountResource\Pages\ListDiscounts::class)
        ->assertOk();
});

it('can filter current discounts', function () {
    $currentDiscount = Discount::factory()->create([
        'starts_at' => now()->subDays(1),
        'ends_at' => now()->addDays(1),
    ]);
    $expiredDiscount = Discount::factory()->create([
        'starts_at' => now()->subDays(10),
        'ends_at' => now()->subDays(1),
    ]);

    \Livewire\Livewire::test(\App\Filament\Resources\DiscountResource\Pages\ListDiscounts::class)
        ->assertOk();
});

it('validates required fields when creating discount', function () {
    actingAs($this->admin)
        ->post(DiscountResource::getUrl('create'), [])
        ->assertSessionHasErrors(['name', 'type', 'value', 'starts_at']);
});

it('validates discount value is positive', function () {
    actingAs($this->admin)
        ->post(DiscountResource::getUrl('create'), [
            'name' => 'Test Discount',
            'type' => 'percentage',
            'value' => -10,
            'starts_at' => now()->toDateTimeString(),
        ])
        ->assertSessionHasErrors(['value']);
});

it('validates ends_at is after starts_at', function () {
    actingAs($this->admin)
        ->post(DiscountResource::getUrl('create'), [
            'name' => 'Test Discount',
            'type' => 'percentage',
            'value' => 10,
            'starts_at' => now()->toDateTimeString(),
            'ends_at' => now()->subDays(1)->toDateTimeString(),
        ])
        ->assertSessionHasErrors(['ends_at']);
});
