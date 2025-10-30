<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CityResource\Pages\CreateCity;
use App\Filament\Resources\CityResource\Pages\ListCities;
use App\Models\City;
use App\Models\Country;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CityResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure the Filament admin panel is fully booted so relationship fields resolve properly.
        $this->resolveAdminPanel();

        // Install the permissions lattice to keep policy checks in sync with production roles.
        $this->seed(RolesAndPermissionsSeeder::class);

        // Create an administrator with super admin rights for unhindered access to city maintenance screens.
        $this->adminUser = User::factory()->create([
            'email'    => 'city-admin@example.test',
            'is_admin' => true,
        ]);
        $this->adminUser->assignRole('super_admin');
    }

    public function test_list_page_displays_cities_from_multiple_countries(): void
    {
        // Attach two cities to distinct countries to validate the eager relationship wiring.
        $vilnius = City::factory()->for(Country::factory()->create(['name' => 'Lithuania']))->create(['name' => 'Vilnius']);
        $riga = City::factory()->for(Country::factory()->create(['name' => 'Latvia']))->create(['name' => 'Riga']);

        Livewire::actingAs($this->adminUser)
            ->test(ListCities::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$vilnius, $riga]);
    }

    public function test_country_filter_limits_table_results(): void
    {
        // Seed two cities but only expect the filter to expose the Lithuanian record.
        $lithuania = Country::factory()->create(['name' => 'Lithuania']);
        $latvia = Country::factory()->create(['name' => 'Latvia']);

        $vilnius = City::factory()->for($lithuania)->create(['name' => 'Vilnius']);
        $riga = City::factory()->for($latvia)->create(['name' => 'Riga']);

        Livewire::actingAs($this->adminUser)
            ->test(ListCities::class)
            ->call('loadTable')
            ->filterTable('country', (string) $lithuania->getKey())
            ->assertCanSeeTableRecords([$vilnius])
            ->assertCanNotSeeTableRecords([$riga]);
    }

    public function test_admin_can_create_city_with_country_metadata(): void
    {
        $country = Country::factory()->create([
            'name'         => 'Lithuania',
            'code'         => 'LT',
            'currency_code'=> 'EUR',
            'language_code'=> 'lt',
            'phone_code'   => '+370',
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(CreateCity::class)
            ->fillForm([
                'name'        => 'Kaunas',
                'slug'        => 'kaunas',
                'country_id'  => $country->getKey(),
                'code'        => 'KAU',
                'description' => 'Second largest Lithuanian city.',
                'latitude'    => '54.8985',
                'longitude'   => '23.9036',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('cities', [
            'slug'       => 'kaunas',
            'country_id' => $country->getKey(),
        ]);
    }
}
