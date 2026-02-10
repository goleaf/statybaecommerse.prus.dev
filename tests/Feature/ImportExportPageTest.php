<?php

declare(strict_types=1);

use App\Filament\Pages\DataImportExport;
use App\Models\AdminUser;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('feature: renders the data import export page', function (): void {
    $user = AdminUser::factory()->create();
    $this->actingAs($user, 'admin');
    $this->get(DataImportExport::getUrl())
        ->assertOk();
});

it('feature: shows only csv import links and no upload form', function (): void {
    $user = AdminUser::factory()->create();
    $this->actingAs($user, 'admin');

    $this->get(DataImportExport::getUrl())
        ->assertOk()
        ->assertDontSee('wire:submit.prevent="import"', false)
        ->assertSee(__('ui.csv_imports'));
});
