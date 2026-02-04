<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Pages;

use App\Filament\Pages\Imports\ImportBrands;
use App\Filament\Pages\Imports\ImportCategories;
use App\Filament\Pages\Imports\ImportCustomers;
use App\Filament\Pages\Imports\ImportDiscounts;
use App\Filament\Pages\Imports\ImportOrders;
use App\Filament\Pages\Imports\ImportOrganizations;
use App\Filament\Pages\Imports\ImportPartners;
use App\Filament\Pages\Imports\ImportPrices;
use App\Filament\Pages\Imports\ImportProducts;
use App\Filament\Pages\Imports\ImportSubscribers;
use App\Filament\Pages\Imports\ImportUsers;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->resolveAdminPanel();
});

dataset('csvImportPages', [
    ImportProducts::class,
    ImportCategories::class,
    ImportBrands::class,
    ImportCustomers::class,
    ImportPartners::class,
    ImportOrganizations::class,
    ImportSubscribers::class,
    ImportUsers::class,
    ImportDiscounts::class,
    ImportPrices::class,
    ImportOrders::class,
]);

it('renders CSV import page for admins', function (string $pageClass) {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test($pageClass)
        ->assertSuccessful()
        ->assertSee(__('filament-actions::import.modal.actions.download_example.label'));
})->with('csvImportPages');

it('forbids non-admin users from CSV import pages', function (string $pageClass) {
    $user = User::factory()->create(['is_admin' => false]);
    $this->actingAs($user);

    $this->get($pageClass::getUrl())
        ->assertRedirect('/admin/login');
})->with('csvImportPages');

it('does not register CSV import pages in navigation', function (string $pageClass) {
    expect($pageClass::shouldRegisterNavigation())->toBeFalse();
})->with('csvImportPages');
