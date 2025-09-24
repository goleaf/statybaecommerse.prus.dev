<?php

declare(strict_types=1);

use App\Filament\Resources\StockResource\Pages\ListStocks;
use App\Models\AdminUser;
use App\Models\Inventory;
use Filament\Facades\Filament;
use Livewire\Livewire;

it('mounts the list page and renders successfully', function (): void {
    Filament::setCurrentPanel('admin');

    $admin = AdminUser::factory()->create();

    test()->actingAs($admin, 'admin');

    Inventory::factory()->create();

    Livewire::test(ListStocks::class)
        ->assertStatus(200);
});
