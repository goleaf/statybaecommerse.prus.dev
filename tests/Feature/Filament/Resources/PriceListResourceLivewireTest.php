<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\PriceListResource\Pages\ListPriceLists;
use App\Models\Currency;
use App\Models\PriceList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Validates the Filament v4 list page for price lists renders seeded data.
 */
final class PriceListResourceLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Filament resolves the admin panel before Livewire boots so panel context matches production.
        $this->resolveAdminPanel();

        // Seed an administrator account that has permission to open catalog resources.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_shows_active_price_list(): void
    {
        // Create a price list with an explicit currency so the table columns hydrate predictable values.
        $currency = Currency::factory()->create([
            'code' => 'EUR',
        ]);
        $priceList = PriceList::factory()->create([
            'name'        => 'Coverage Price List',
            'currency_id' => $currency->getKey(),
            'is_enabled'  => true,
        ]);

        // Load the Filament list page and confirm the generated price list record is visible to the administrator.
        Livewire::test(ListPriceLists::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$priceList]);
    }
}
