<?php

declare(strict_types=1);

use App\Filament\Resources\VariantPriceHistoryResource\Pages\CreateVariantPriceHistory;
use App\Filament\Resources\VariantPriceHistoryResource\Pages\EditVariantPriceHistory;
use App\Filament\Resources\VariantPriceHistoryResource\Pages\ListVariantPriceHistories;
use App\Filament\Resources\VariantPriceHistoryResource\Pages\ViewVariantPriceHistory;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantPriceHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminUser = User::factory()->create([
        'email' => 'admin@example.com',
        'is_admin' => true,
    ]);
});

it('can list variant price histories', function () {
    $variant = ProductVariant::factory()->create();
    $priceHistory = VariantPriceHistory::factory()->create([
        'variant_id' => $variant->id,
        'old_price' => 10.00,
        'new_price' => 12.00,
        'price_type' => 'regular',
        'change_reason' => 'manual',
        'changed_by' => $this->adminUser->id,
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(ListVariantPriceHistories::class)
        ->assertCanSeeTableRecords([$priceHistory])
        ->assertCanRenderTableColumn('variant.name')
        ->assertCanRenderTableColumn('old_price')
        ->assertCanRenderTableColumn('new_price')
        ->assertCanRenderTableColumn('price_type')
        ->assertCanRenderTableColumn('change_reason');
});

it('can create a variant price history', function () {
    $variant = ProductVariant::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(CreateVariantPriceHistory::class)
        ->fillForm([
            'variant_id' => $variant->id,
            'old_price' => 10.00,
            'new_price' => 12.00,
            'price_type' => 'regular',
            'change_reason' => 'manual',
            'changed_by' => $this->adminUser->id,
            'effective_from' => now(),
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('variant_price_history', [
        'variant_id' => $variant->id,
        'old_price' => 10.00,
        'new_price' => 12.00,
        'price_type' => 'regular',
        'change_reason' => 'manual',
        'changed_by' => $this->adminUser->id,
    ]);
});

it('can view a variant price history', function () {
    $variant = ProductVariant::factory()->create();
    $priceHistory = VariantPriceHistory::factory()->create([
        'variant_id' => $variant->id,
        'old_price' => 10.00,
        'new_price' => 12.00,
        'price_type' => 'regular',
        'change_reason' => 'manual',
        'changed_by' => $this->adminUser->id,
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(ViewVariantPriceHistory::class, ['record' => $priceHistory->id])
        ->assertOk();
});

it('can edit a variant price history', function () {
    $variant = ProductVariant::factory()->create();
    $priceHistory = VariantPriceHistory::factory()->create([
        'variant_id' => $variant->id,
        'old_price' => 10.00,
        'new_price' => 12.00,
        'price_type' => 'regular',
        'change_reason' => 'manual',
        'changed_by' => $this->adminUser->id,
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(EditVariantPriceHistory::class, ['record' => $priceHistory->id])
        ->fillForm([
            'old_price' => 10.00,
            'new_price' => 15.00,
            'price_type' => 'sale',
            'change_reason' => 'promotion',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('variant_price_history', [
        'id' => $priceHistory->id,
        'old_price' => 10.00,
        'new_price' => 15.00,
        'price_type' => 'sale',
        'change_reason' => 'promotion',
    ]);
});

it('can delete a variant price history', function () {
    $variant = ProductVariant::factory()->create();
    $priceHistory = VariantPriceHistory::factory()->create([
        'variant_id' => $variant->id,
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(EditVariantPriceHistory::class, ['record' => $priceHistory->id])
        ->callAction('delete')
        ->assertHasNoActionErrors();

    $this->assertDatabaseMissing('variant_price_history', [
        'id' => $priceHistory->id,
    ]);
});

it('validates required fields when creating', function () {
    Livewire::actingAs($this->adminUser)
        ->test(CreateVariantPriceHistory::class)
        ->fillForm([
            'variant_id' => null,
            'new_price' => null,
            'price_type' => null,
            'change_reason' => null,
            'effective_from' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'variant_id' => 'required',
            'new_price' => 'required',
            'price_type' => 'required',
            'change_reason' => 'required',
            'effective_from' => 'required',
        ]);
});

it('validates numeric fields', function () {
    $variant = ProductVariant::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(CreateVariantPriceHistory::class)
        ->fillForm([
            'variant_id' => $variant->id,
            'old_price' => 'invalid',
            'new_price' => 'invalid',
            'price_type' => 'regular',
            'change_reason' => 'manual',
            'effective_from' => now(),
        ])
        ->call('create')
        ->assertHasFormErrors([
            'old_price' => 'numeric',
            'new_price' => 'numeric',
        ]);
});

it('validates minimum values for prices', function () {
    $variant = ProductVariant::factory()->create();

    Livewire::actingAs($this->adminUser)
        ->test(CreateVariantPriceHistory::class)
        ->fillForm([
            'variant_id' => $variant->id,
            'old_price' => -1,
            'new_price' => -1,
            'price_type' => 'regular',
            'change_reason' => 'manual',
            'effective_from' => now(),
        ])
        ->call('create')
        ->assertHasFormErrors([
            'old_price' => 'min',
            'new_price' => 'min',
        ]);
});

it('can filter by price type', function () {
    $variant = ProductVariant::factory()->create();

    VariantPriceHistory::factory()->create([
        'variant_id' => $variant->id,
        'price_type' => 'regular',
    ]);

    VariantPriceHistory::factory()->create([
        'variant_id' => $variant->id,
        'price_type' => 'sale',
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(ListVariantPriceHistories::class)
        ->filterTable('price_type', 'regular')
        ->assertCanSeeTableRecords(
            VariantPriceHistory::where('price_type', 'regular')->get()
        )
        ->assertCanNotSeeTableRecords(
            VariantPriceHistory::where('price_type', 'sale')->get()
        );
});

it('can filter by change reason', function () {
    $variant = ProductVariant::factory()->create();

    VariantPriceHistory::factory()->create([
        'variant_id' => $variant->id,
        'change_reason' => 'manual',
    ]);

    VariantPriceHistory::factory()->create([
        'variant_id' => $variant->id,
        'change_reason' => 'automatic',
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(ListVariantPriceHistories::class)
        ->filterTable('change_reason', 'manual')
        ->assertCanSeeTableRecords(
            VariantPriceHistory::where('change_reason', 'manual')->get()
        )
        ->assertCanNotSeeTableRecords(
            VariantPriceHistory::where('change_reason', 'automatic')->get()
        );
});

it('can search by variant name', function () {
    $variant1 = ProductVariant::factory()->create(['name' => 'Test Variant 1']);
    $variant2 = ProductVariant::factory()->create(['name' => 'Test Variant 2']);

    VariantPriceHistory::factory()->create(['variant_id' => $variant1->id]);
    VariantPriceHistory::factory()->create(['variant_id' => $variant2->id]);

    Livewire::actingAs($this->adminUser)
        ->test(ListVariantPriceHistories::class)
        ->searchTable('Test Variant 1')
        ->assertCanSeeTableRecords(
            VariantPriceHistory::whereHas('variant', fn ($q) => $q->where('name', 'like', '%Test Variant 1%'))->get()
        );
});

it('can sort by effective date', function () {
    $variant = ProductVariant::factory()->create();

    $oldRecord = VariantPriceHistory::factory()->create([
        'variant_id' => $variant->id,
        'effective_from' => now()->subDays(2),
    ]);

    $newRecord = VariantPriceHistory::factory()->create([
        'variant_id' => $variant->id,
        'effective_from' => now(),
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(ListVariantPriceHistories::class)
        ->sortTable('effective_from', 'desc')
        ->assertCanSeeTableRecords([$newRecord, $oldRecord], inOrder: true);
});

it('calculates price change correctly', function () {
    $variant = ProductVariant::factory()->create();
    $priceHistory = VariantPriceHistory::factory()->create([
        'variant_id' => $variant->id,
        'old_price' => 10.00,
        'new_price' => 12.00,
    ]);

    expect($priceHistory->getChangeAmountAttribute())->toBe(2.00);
    expect($priceHistory->getChangePercentageAttribute())->toBe(20.0);
    expect($priceHistory->isIncrease())->toBeTrue();
    expect($priceHistory->isDecrease())->toBeFalse();
});

it('handles price decreases correctly', function () {
    $variant = ProductVariant::factory()->create();
    $priceHistory = VariantPriceHistory::factory()->create([
        'variant_id' => $variant->id,
        'old_price' => 12.00,
        'new_price' => 10.00,
    ]);

    expect($priceHistory->getChangeAmountAttribute())->toBe(-2.00);
    expect($priceHistory->getChangePercentageAttribute())->toBe(-16.67);
    expect($priceHistory->isIncrease())->toBeFalse();
    expect($priceHistory->isDecrease())->toBeTrue();
});
