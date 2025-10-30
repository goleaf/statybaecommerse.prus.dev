<?php

declare(strict_types=1);

use App\Filament\Resources\PriceResource\Pages\ListPrices;
use App\Models\Price;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Resolve the Filament panel so Livewire pages boot with all dependencies.
    $this->resolveAdminPanel();

    // Authenticate a deterministic administrator before exercising Filament resources.
    $this->adminUser = User::factory()->create([
        'email'    => 'admin@example.com',
        'is_admin' => true,
    ]);

    actingAs($this->adminUser);
});

it('feature: lists product prices inside the Filament table', function (): void {
    $product = Product::factory()->create(['name' => 'Livewire Priced Product']);

    // Seed a concrete price record to verify the table renders real data.
    $price = Price::factory()->create([
        'product_id' => $product->id,
        'amount'     => 49.99,
        'currency'   => 'EUR',
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(ListPrices::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords([$price]);
});
