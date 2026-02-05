<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\DataImportExport;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->resolveAdminPanel();
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('can render data import export page', function () {
    Livewire::test(DataImportExport::class)
        ->assertSuccessful();
});

it('exposes the expected navigation icon', function () {
    expect(DataImportExport::getNavigationIcon())->toBe('heroicon-o-arrow-up-tray');
});

it('forbids guest to access data import export page', function () {
    auth()->logout();

    $this->get(DataImportExport::getUrl())
        ->assertRedirect('/admin/login');
});

it('forbids non-admin user to access data import export page', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $this->actingAs($user);

    $this->get(DataImportExport::getUrl())
        ->assertRedirect('/admin/login');
});

it('displays the stats on the page', function () {
    Livewire::test(DataImportExport::class)
        ->assertSee('fi-wi-stats-overview')
        ->assertSee(__('messages.admin_products'))
        ->assertSee(__('messages.admin_categories'))
        ->assertSee(__('messages.admin_brands'))
        ->assertSee(__('messages.users'))
        ->assertSee(__('messages.admin_orders'));
});

it('shows CSV import links', function () {
    $productsImportLabel = __('translations.import') . ' ' . __('translations.products');

    Livewire::test(DataImportExport::class)
        ->assertSee($productsImportLabel)
        ->assertSee(__('admin.categories_import'))
        ->assertSee(__('admin.brands_import'))
        ->assertSee(__('admin.customers_import'))
        ->assertSee(__('admin.partners_import'))
        ->assertSee(__('admin.organizations_import'))
        ->assertSee(__('admin.subscribers_import'))
        ->assertSee(__('admin.users_import'))
        ->assertSee(__('admin.discounts_import'))
        ->assertSee(__('admin.prices_import'))
        ->assertSee(__('admin.orders_import'));
});
