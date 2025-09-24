<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CountryResource;
use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CountryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]));
    }

    public function test_can_list_countries(): void
    {
        $countries = Country::factory()->count(3)->create();

        Livewire::test(CountryResource\Pages\ListCountries::class)
            ->assertCanSeeTableRecords($countries);
    }

    public function test_can_create_country(): void
    {
        $countryData = [
            'name' => 'Test Country',
            'name_official' => 'Official Test Country',
            'cca2' => 'TC',
            'cca3' => 'TCO',
            'ccn3' => '123',
            'iso_code' => 'TC-001',
            'region' => 'Test Region',
            'subregion' => 'Test Subregion',
            'currency_code' => 'TCD',
            'currency_symbol' => 'T$',
            'phone_calling_code' => '+123',
            'is_active' => true,
            'is_eu_member' => false,
            'requires_vat' => true,
            'vat_rate' => 20.0,
        ];

        Livewire::test(CountryResource\Pages\CreateCountry::class)
            ->fillForm($countryData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('countries', [
            'name' => 'Test Country',
            'cca2' => 'TC',
        ]);
    }

    public function test_can_edit_country(): void
    {
        $country = Country::factory()->create([
            'name' => 'Original Name',
            'cca2' => 'ON',
        ]);

        Livewire::test(CountryResource\Pages\EditCountry::class, [
            'record' => $country->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Name',
                'cca2' => 'UN',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $country->refresh();
        $this->assertEquals('Updated Name', $country->name);
        $this->assertEquals('UN', $country->cca2);
    }

    public function test_can_view_country(): void
    {
        $country = Country::factory()->create([
            'name' => 'View Test Country',
            'cca2' => 'VT',
        ]);

        Livewire::test(CountryResource\Pages\ViewCountry::class, [
            'record' => $country->getRouteKey(),
        ])
            ->assertCanSeeText('View Test Country')
            ->assertCanSeeText('VT');
    }

    public function test_can_delete_country(): void
    {
        $country = Country::factory()->create();

        Livewire::test(CountryResource\Pages\ListCountries::class)
            ->callTableAction('delete', $country);

        $this->assertSoftDeleted('countries', [
            'id' => $country->id,
        ]);
    }

    public function test_can_bulk_delete_countries(): void
    {
        $countries = Country::factory()->count(3)->create();

        Livewire::test(CountryResource\Pages\ListCountries::class)
            ->callTableBulkAction('delete', $countries);

        foreach ($countries as $country) {
            $this->assertSoftDeleted('countries', [
                'id' => $country->id,
            ]);
        }
    }

    public function test_can_filter_countries_by_region(): void
    {
        Country::factory()->create(['region' => 'Europe']);
        Country::factory()->create(['region' => 'Asia']);

        Livewire::test(CountryResource\Pages\ListCountries::class)
            ->filterTable('region', 'Europe')
            ->assertCanSeeTableRecords(Country::where('region', 'Europe')->get())
            ->assertCanNotSeeTableRecords(Country::where('region', 'Asia')->get());
    }

    public function test_can_filter_countries_by_eu_member_status(): void
    {
        Country::factory()->create(['is_eu_member' => true]);
        Country::factory()->create(['is_eu_member' => false]);

        Livewire::test(CountryResource\Pages\ListCountries::class)
            ->filterTable('is_eu_member', true)
            ->assertCanSeeTableRecords(Country::where('is_eu_member', true)->get());
    }

    public function test_can_filter_countries_by_active_status(): void
    {
        Country::factory()->create(['is_active' => true]);
        Country::factory()->create(['is_active' => false]);

        Livewire::test(CountryResource\Pages\ListCountries::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords(Country::where('is_active', true)->get());
    }

    public function test_can_search_countries_by_name(): void
    {
        Country::factory()->create(['name' => 'Lithuania']);
        Country::factory()->create(['name' => 'Latvia']);

        Livewire::test(CountryResource\Pages\ListCountries::class)
            ->searchTable('Lithuania')
            ->assertCanSeeTableRecords(Country::where('name', 'like', '%Lithuania%')->get());
    }

    public function test_can_activate_country(): void
    {
        $country = Country::factory()->create(['is_active' => false]);

        Livewire::test(CountryResource\Pages\ListCountries::class)
            ->callTableAction('activate', $country);

        $country->refresh();
        $this->assertTrue($country->is_active);
    }

    public function test_can_deactivate_country(): void
    {
        $country = Country::factory()->create(['is_active' => true]);

        Livewire::test(CountryResource\Pages\ListCountries::class)
            ->callTableAction('deactivate', $country);

        $country->refresh();
        $this->assertFalse($country->is_active);
    }

    public function test_can_bulk_activate_countries(): void
    {
        $countries = Country::factory()->count(3)->create(['is_active' => false]);

        Livewire::test(CountryResource\Pages\ListCountries::class)
            ->callTableBulkAction('activate', $countries);

        foreach ($countries as $country) {
            $country->refresh();
            $this->assertTrue($country->is_active);
        }
    }

    public function test_can_bulk_deactivate_countries(): void
    {
        $countries = Country::factory()->count(3)->create(['is_active' => true]);

        Livewire::test(CountryResource\Pages\ListCountries::class)
            ->callTableBulkAction('deactivate', $countries);

        foreach ($countries as $country) {
            $country->refresh();
            $this->assertFalse($country->is_active);
        }
    }

    public function test_country_validation_requires_name(): void
    {
        Livewire::test(CountryResource\Pages\CreateCountry::class)
            ->fillForm([
                'name' => '',
                'cca2' => 'TC',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_country_validation_requires_cca2(): void
    {
        Livewire::test(CountryResource\Pages\CreateCountry::class)
            ->fillForm([
                'name' => 'Test Country',
                'cca2' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['cca2' => 'required']);
    }

    public function test_country_validation_cca2_must_be_unique(): void
    {
        Country::factory()->create(['cca2' => 'TC']);

        Livewire::test(CountryResource\Pages\CreateCountry::class)
            ->fillForm([
                'name' => 'Test Country',
                'cca2' => 'TC',
            ])
            ->call('create')
            ->assertHasFormErrors(['cca2' => 'unique']);
    }

    public function test_country_validation_cca2_max_length(): void
    {
        Livewire::test(CountryResource\Pages\CreateCountry::class)
            ->fillForm([
                'name' => 'Test Country',
                'cca2' => 'TOOLONG',
            ])
            ->call('create')
            ->assertHasFormErrors(['cca2' => 'max']);
    }

    public function test_country_validation_vat_rate_numeric(): void
    {
        Livewire::test(CountryResource\Pages\CreateCountry::class)
            ->fillForm([
                'name' => 'Test Country',
                'cca2' => 'TC',
                'vat_rate' => 'invalid',
            ])
            ->call('create')
            ->assertHasFormErrors(['vat_rate' => 'numeric']);
    }

    public function test_country_validation_vat_rate_range(): void
    {
        Livewire::test(CountryResource\Pages\CreateCountry::class)
            ->fillForm([
                'name' => 'Test Country',
                'cca2' => 'TC',
                'vat_rate' => 150.0,
            ])
            ->call('create')
            ->assertHasFormErrors(['vat_rate' => 'max']);
    }

    public function test_country_relationships_cities_count(): void
    {
        $country = Country::factory()->create();
        $country->cities()->create([
            'name' => 'Test City',
            'country_id' => $country->id,
        ]);

        Livewire::test(CountryResource\Pages\ListCountries::class)
            ->assertCanSeeText('1');  // cities count
    }

    public function test_country_relationships_addresses_count(): void
    {
        $country = Country::factory()->create();
        $country->addresses()->create([
            'street' => 'Test Street',
            'city' => 'Test City',
            'country_code' => $country->cca2,
        ]);

        Livewire::test(CountryResource\Pages\ListCountries::class)
            ->assertCanSeeText('1');  // addresses count
    }

    public function test_country_global_search(): void
    {
        $country = Country::factory()->create([
            'name' => 'Lithuania',
            'cca2' => 'LT',
            'region' => 'Europe',
        ]);

        $searchResults = CountryResource::getGlobalSearchResultDetails($country);

        $this->assertArrayHasKey('Code', $searchResults);
        $this->assertArrayHasKey('Region', $searchResults);
        $this->assertEquals('LT', $searchResults['Code']);
        $this->assertEquals('Europe', $searchResults['Region']);
    }

    public function test_country_global_search_actions(): void
    {
        $country = Country::factory()->create();

        $actions = CountryResource::getGlobalSearchResultActions($country);

        $this->assertIsArray($actions);
        $this->assertNotEmpty($actions);
    }

    public function test_country_scope_active(): void
    {
        Country::factory()->create(['is_active' => true]);
        Country::factory()->create(['is_active' => false]);

        $activeCountries = Country::active()->get();
        $this->assertCount(1, $activeCountries);
        $this->assertTrue($activeCountries->first()->is_active);
    }

    public function test_country_scope_eu_members(): void
    {
        Country::factory()->create(['is_eu_member' => true]);
        Country::factory()->create(['is_eu_member' => false]);

        $euCountries = Country::euMembers()->get();
        $this->assertCount(1, $euCountries);
        $this->assertTrue($euCountries->first()->is_eu_member);
    }

    public function test_country_scope_requires_vat(): void
    {
        Country::factory()->create(['requires_vat' => true]);
        Country::factory()->create(['requires_vat' => false]);

        $vatCountries = Country::requiresVat()->get();
        $this->assertCount(1, $vatCountries);
        $this->assertTrue($vatCountries->first()->requires_vat);
    }

    public function test_country_scope_by_region(): void
    {
        Country::factory()->create(['region' => 'Europe']);
        Country::factory()->create(['region' => 'Asia']);

        $europeCountries = Country::byRegion('Europe')->get();
        $this->assertCount(1, $europeCountries);
        $this->assertEquals('Europe', $europeCountries->first()->region);
    }

    public function test_country_scope_by_currency(): void
    {
        Country::factory()->create(['currency_code' => 'EUR']);
        Country::factory()->create(['currency_code' => 'USD']);

        $eurCountries = Country::byCurrency('EUR')->get();
        $this->assertCount(1, $eurCountries);
        $this->assertEquals('EUR', $eurCountries->first()->currency_code);
    }

    public function test_country_helper_methods(): void
    {
        $country = Country::factory()->create([
            'is_active' => true,
            'is_eu_member' => true,
            'requires_vat' => true,
            'vat_rate' => 20.0,
        ]);

        $this->assertTrue($country->isActive());
        $this->assertTrue($country->isEuMember());
        $this->assertTrue($country->requiresVat());
        $this->assertEquals(20.0, $country->getVatRate());
        $this->assertEquals('20.00%', $country->getFormattedVatRate());
    }

    public function test_country_display_name(): void
    {
        $country = Country::factory()->create([
            'name' => 'Lithuania',
            'phone_calling_code' => '370',
        ]);

        $this->assertEquals('Lithuania (+370)', $country->display_name);
    }

    public function test_country_coordinates(): void
    {
        $country = Country::factory()->create([
            'latitude' => 54.6872,
            'longitude' => 25.2797,
        ]);

        $coordinates = $country->coordinates;
        $this->assertEquals(54.6872, $coordinates['latitude']);
        $this->assertEquals(25.2797, $coordinates['longitude']);
    }

    public function test_country_economic_info(): void
    {
        $country = Country::factory()->create([
            'currency_code' => 'EUR',
            'currency_symbol' => '€',
            'requires_vat' => true,
            'vat_rate' => 21.0,
            'is_eu_member' => true,
        ]);

        $economicInfo = $country->getEconomicInfo();

        $this->assertArrayHasKey('currency', $economicInfo);
        $this->assertArrayHasKey('vat', $economicInfo);
        $this->assertArrayHasKey('eu_member', $economicInfo);
        $this->assertTrue($economicInfo['eu_member']);
    }

    public function test_country_geographic_info(): void
    {
        $country = Country::factory()->create([
            'region' => 'Europe',
            'subregion' => 'Northern Europe',
            'latitude' => 54.6872,
            'longitude' => 25.2797,
            'timezone' => 'Europe/Vilnius',
        ]);

        $geographicInfo = $country->getGeographicInfo();

        $this->assertEquals('Europe', $geographicInfo['region']);
        $this->assertEquals('Northern Europe', $geographicInfo['subregion']);
        $this->assertEquals('Europe/Vilnius', $geographicInfo['timezone']);
        $this->assertArrayHasKey('coordinates', $geographicInfo);
    }
}
