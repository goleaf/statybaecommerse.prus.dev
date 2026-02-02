<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\DataImportExport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Filament\Actions\ImportAction;

beforeEach(function () {
    $this->resolveAdminPanel();
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('can render data import export page', function () {
    Livewire::test(DataImportExport::class)
        ->assertSuccessful();
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

it('has import products action', function () {
    Livewire::test(DataImportExport::class)
        ->assertActionExists('importProducts');
});

it('has import categories action', function () {
    Livewire::test(DataImportExport::class)
        ->assertActionExists('importCategories');
});

it('has import brands action', function () {
    Livewire::test(DataImportExport::class)
        ->assertActionExists('importBrands');
});

it('can open import products modal', function () {
    Livewire::test(DataImportExport::class)
        ->callAction('importProducts');
});

it('displays the stats on the page', function () {
    Livewire::test(DataImportExport::class)
        ->assertSee(__('messages.admin_products'))
        ->assertSee(__('messages.admin_categories'))
        ->assertSee(__('messages.admin_brands'))
        ->assertSee(__('messages.users'))
        ->assertSee(__('messages.admin_orders'));
});

it('has the legacy import action', function () {
    Livewire::test(DataImportExport::class)
        ->assertActionExists('import');
});