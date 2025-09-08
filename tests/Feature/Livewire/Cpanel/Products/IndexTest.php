<?php declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

it('renders cpanel products index and shows table', function (): void {
    $this->withoutVite();

    $adminUser = User::factory()->create(['email' => 'admin@admin.com']);

    $this->actingAs($adminUser);

    $component = Livewire::test(\App\Livewire\Cpanel\Products\Index::class);

    $component->assertOk();
    $component->assertSeeText('Products');
})->group('cpanel');

it('toggles visibility via table action and refreshes', function (): void {
    $this->withoutVite();

    $adminUser = User::factory()->create(['email' => 'admin@admin.com']);
    $this->actingAs($adminUser);

    $product = Product::factory()->create(['is_visible' => false]);

    $component = Livewire::test(\App\Livewire\Cpanel\Products\Index::class)
        ->callTableAction('toggle_visibility', $product);

    expect($product->refresh()->is_visible)->toBeTrue();
});

it('filters by is_visible and by stock range', function (): void {
    $this->withoutVite();

    $adminUser = User::factory()->create(['email' => 'admin@admin.com']);
    $this->actingAs($adminUser);

    $visible = Product::factory()->create(['is_visible' => true, 'warehouse_quantity' => 5]);
    $hidden = Product::factory()->create(['is_visible' => false, 'warehouse_quantity' => 0]);

    Livewire::test(\App\Livewire\Cpanel\Products\Index::class)
        ->filterTable('is_visible')
        ->assertCanSeeTableRecords([$visible])
        ->assertCanNotSeeTableRecords([$hidden])
        ->setTableFilters([
            'warehouse_quantity_range' => ['min' => '4', 'max' => '10'],
        ])
        ->assertCanSeeTableRecords([$visible])
        ->assertCanNotSeeTableRecords([$hidden]);
});
