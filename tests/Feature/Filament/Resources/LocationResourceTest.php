<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages\ListLocations;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class LocationResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Bind the Filament admin panel so resource URLs resolve during Livewire bootstrapping.
        $this->resolveAdminPanel();

        // Keep translations predictable for select inputs that surface locale-specific copy.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Sign in as an administrator to satisfy any policy checks enforced by the resource.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_locations(): void
    {
        // Create a concrete location record so the list view has content to render.
        $location = Location::factory()->create([
            'name'       => 'Vilnius Distribution Hub',
            'code'       => 'VIL-001',
            'type'       => 'warehouse',
            'is_enabled' => true,
        ]);

        // Ensure the Livewire table loads and includes the seeded location.
        Livewire::test(ListLocations::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$location]);
    }

    public function test_table_filters_handle_type_and_coordinate_toggles(): void
    {
        // Generate contrasting fixtures so each filter produces observable differences.
        $warehouse = Location::factory()->create([
            'name'         => 'Kaunas Warehouse',
            'code'         => 'KAU-001',
            'type'         => 'warehouse',
            'latitude'     => 54.8985,
            'longitude'    => 23.9036,
            'opening_hours' => [
                ['day' => 'monday', 'is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'],
            ],
            'is_enabled'   => true,
        ]);

        $store = Location::factory()->create([
            'name'         => 'Vilnius Store',
            'code'         => 'VIL-010',
            'type'         => 'store',
            'latitude'     => null,
            'longitude'    => null,
            'opening_hours' => null,
            'is_enabled'   => true,
        ]);

        // Scope the dataset by type to isolate warehouses and exclude store records.
        Livewire::test(ListLocations::class)
            ->call('loadTable')
            ->filterTable('type', 'warehouse')
            ->assertSee('Kaunas Warehouse')
            ->assertDontSee('Vilnius Store');

        // Verify the coordinate selector distinguishes between geocoded and non-geocoded records.
        Livewire::test(ListLocations::class)
            ->call('loadTable')
            ->filterTable('has_coordinates', 'yes')
            ->assertSee('Kaunas Warehouse')
            ->assertDontSee('Vilnius Store')
            ->filterTable('has_coordinates', 'no')
            ->assertSee('Vilnius Store')
            ->assertDontSee('Kaunas Warehouse');
    }

    public function test_table_filters_detect_opening_hours_presence(): void
    {
        // Seed examples with and without structured opening hours metadata.
        $withHours = Location::factory()->create([
            'name'          => 'Panevezys Depot',
            'code'          => 'PAN-500',
            'opening_hours' => [
                ['day' => 'friday', 'is_closed' => false, 'open_time' => '08:00', 'close_time' => '16:00'],
            ],
        ]);

        $withoutHours = Location::factory()->create([
            'name'          => 'Siauliai Satellite',
            'code'          => 'SIA-250',
            'opening_hours' => null,
        ]);

        // Verify the opening-hours select filter separates the two fixture groups correctly.
        Livewire::test(ListLocations::class)
            ->call('loadTable')
            ->filterTable('has_opening_hours', 'yes')
            ->assertSee('Panevezys Depot')
            ->assertDontSee('Siauliai Satellite')
            ->filterTable('has_opening_hours', 'no')
            ->assertSee('Siauliai Satellite')
            ->assertDontSee('Panevezys Depot');
    }
}
