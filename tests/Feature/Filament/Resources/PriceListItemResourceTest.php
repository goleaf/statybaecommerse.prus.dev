<?php

declare(strict_types=1);

use App\Filament\Resources\PriceListItemResource;
use App\Filament\Resources\PriceListItemResource\Pages\ListPriceListItems;
use App\Models\PriceListItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('mounts the PriceListItemResource index page', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $this
        ->get(PriceListItemResource::getUrl('index'))
        ->assertOk();
});

it('includes items with a null valid_from value when filtering for currently valid entries', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $nullStartItem = PriceListItem::factory()->create([
        'valid_from' => null,
        'valid_until' => now()->addDay(),
        'is_active' => true,
    ]);

    $futureItem = PriceListItem::factory()->create([
        'valid_from' => now()->addDay(),
        'valid_until' => now()->addDays(2),
        'is_active' => true,
    ]);

    Livewire::test(ListPriceListItems::class)
        ->filterTable('valid_now')
        ->assertCanSeeTableRecords([$nullStartItem])
        ->assertCanNotSeeTableRecords([$futureItem]);
});
