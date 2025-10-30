<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\PriceResource\Pages\ListPrices;
use App\Models\Currency;
use App\Models\Price;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Ensures the Filament price listing renders without regressions after the v4 upgrade.
 */
final class PriceResourceLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Prepare the Filament panel context before Livewire tries to resolve pages.
        $this->resolveAdminPanel();

        // Create an administrator so the pricing resource is accessible during assertions.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_surfaces_recent_price_record(): void
    {
        // Ensure a matching currency exists for the factory hard-coded identifier.
        Currency::factory()->create([
            'id'   => 1,
            'code' => 'EUR',
        ]);

        // Persist a price entry for a related product so the list has data to render.
        $price = Price::factory()->create([
            'amount' => 129.99,
        ]);

        // Mount the Livewire page and verify the record is visible after the table loads.
        Livewire::test(ListPrices::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$price]);
    }
}
