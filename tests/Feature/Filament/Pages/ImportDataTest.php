<?php

declare(strict_types=1);

use App\Filament\Pages\DataImportExport;
use App\Filament\Pages\Imports\ImportCategories;
use App\Models\AdminUser;

use function Pest\Livewire\livewire;

it('can render import data page', function () {
    $user = AdminUser::factory()->create();

    $this->actingAs($user, 'admin')
        ->get(DataImportExport::getUrl())
        ->assertSuccessful();
});

it('can render import categories page', function () {
    $user = AdminUser::factory()->create();

    $this->actingAs($user, 'admin')
        ->get(ImportCategories::getUrl())
        ->assertSuccessful();
});

it('exposes csv import pages on the data import dashboard', function () {
    $user = AdminUser::factory()->create();

    $this->actingAs($user, 'admin');

    $pages = livewire(DataImportExport::class)->instance()->getCsvImportPages();

    expect(collect($pages)->pluck('url'))
        ->toContain(ImportCategories::getUrl());
});

it('is forbidden for guests', function () {
    $this->get(DataImportExport::getUrl())
        ->assertRedirect('/admin/login');
});
