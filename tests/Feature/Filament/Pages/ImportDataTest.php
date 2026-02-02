<?php

use App\Filament\Pages\ImportData;
use App\Models\AdminUser;
use Filament\Actions\ImportAction;

use function Pest\Livewire\livewire;

it('can render import data page', function () {
    $user = AdminUser::factory()->create();

    $this->actingAs($user, 'admin')
        ->get(ImportData::getUrl())
        ->assertSuccessful();
});

it('can mount import actions', function () {
    $user = AdminUser::factory()->create();

    $this->actingAs($user, 'admin');

    livewire(ImportData::class)
        ->assertActionExists('importProducts')
        ->assertActionExists('importBrands')
        ->assertActionExists('importCategories');
});

it('is forbidden for guests', function () {
    $this->get(ImportData::getUrl())
        ->assertRedirect('/admin/login');
});
