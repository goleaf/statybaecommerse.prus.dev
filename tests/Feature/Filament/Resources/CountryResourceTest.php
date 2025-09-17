<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Models\Country;
use App\Models\Translations\CountryTranslation;
use App\Models\User;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CountryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_countries_resource_can_list_countries(): void
    {
        Country::factory()->count(3)->create();

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\ListCountries::class)
            ->assertCanSeeTableRecords(Country::all());
    }

    public function test_countries_resource_can_create_country(): void
    {
        $countryData = [
            'name' => 'Lithuania',
            'name_official' => 'Republic of Lithuania',
            'cca2' => 'LT',
            'cca3' => 'LTU',
            'region' => 'Europe',
            'currency_code' => 'EUR',
            'is_active' => true,
            'is_enabled' => true,
        ];

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\CreateCountry::class)
            ->fillForm($countryData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('countries', [
            'name' => 'Lithuania',
            'name_official' => 'Republic of Lithuania',
            'cca2' => 'LT',
            'cca3' => 'LTU',
            'region' => 'Europe',
            'currency_code' => 'EUR',
            'is_active' => true,
            'is_enabled' => true,
        ]);
    }

    public function test_countries_resource_can_edit_country(): void
    {
        $country = Country::factory()->create([
            'name' => 'Lithuania',
            'cca2' => 'LT',
        ]);

        $updatedData = [
            'name' => 'Lietuva',
            'cca2' => 'LT',
            'region' => 'Europe',
        ];

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\EditCountry::class, [
            'record' => $country->getRouteKey(),
        ])
            ->fillForm($updatedData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('countries', [
            'id' => $country->id,
            'name' => 'Lietuva',
            'cca2' => 'LT',
            'region' => 'Europe',
        ]);
    }

    public function test_countries_resource_can_view_country(): void
    {
        $country = Country::factory()->create([
            'name' => 'Lithuania',
            'cca2' => 'LT',
            'region' => 'Europe',
        ]);

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\ViewCountry::class, [
            'record' => $country->getRouteKey(),
        ])
            ->assertFormSet([
                'name' => 'Lithuania',
                'cca2' => 'LT',
                'region' => 'Europe',
            ]);
    }

    public function test_countries_resource_can_delete_country(): void
    {
        $country = Country::factory()->create();

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\ListCountries::class)
            ->callTableAction('delete', $country);

        $this->assertSoftDeleted('countries', ['id' => $country->id]);
    }

    public function test_countries_resource_can_bulk_delete_countries(): void
    {
        $countries = Country::factory()->count(3)->create();

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\ListCountries::class)
            ->callTableBulkAction('delete', $countries);

        foreach ($countries as $country) {
            $this->assertSoftDeleted('countries', ['id' => $country->id]);
        }
    }

    public function test_countries_resource_can_bulk_activate_countries(): void
    {
        $countries = Country::factory()->count(3)->create(['is_active' => false]);

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\ListCountries::class)
            ->callTableBulkAction('activate', $countries);

        foreach ($countries as $country) {
            $this->assertDatabaseHas('countries', [
                'id' => $country->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_countries_resource_can_bulk_deactivate_countries(): void
    {
        $countries = Country::factory()->count(3)->create(['is_active' => true]);

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\ListCountries::class)
            ->callTableBulkAction('deactivate', $countries);

        foreach ($countries as $country) {
            $this->assertDatabaseHas('countries', [
                'id' => $country->id,
                'is_active' => false,
            ]);
        }
    }

    public function test_countries_resource_can_filter_by_active_status(): void
    {
        Country::factory()->create(['is_active' => true]);
        Country::factory()->create(['is_active' => false]);

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\ListCountries::class)
            ->filterTable('is_active', '1')
            ->assertCanSeeTableRecords(Country::where('is_active', true)->get())
            ->assertCanNotSeeTableRecords(Country::where('is_active', false)->get());
    }

    public function test_countries_resource_can_filter_by_eu_member_status(): void
    {
        Country::factory()->create(['is_eu_member' => true]);
        Country::factory()->create(['is_eu_member' => false]);

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\ListCountries::class)
            ->filterTable('is_eu_member', '1')
            ->assertCanSeeTableRecords(Country::where('is_eu_member', true)->get())
            ->assertCanNotSeeTableRecords(Country::where('is_eu_member', false)->get());
    }

    public function test_countries_resource_can_filter_by_region(): void
    {
        Country::factory()->create(['region' => 'Europe']);
        Country::factory()->create(['region' => 'Asia']);

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\ListCountries::class)
            ->filterTable('region', 'Europe')
            ->assertCanSeeTableRecords(Country::where('region', 'Europe')->get())
            ->assertCanNotSeeTableRecords(Country::where('region', 'Asia')->get());
    }

    public function test_countries_resource_can_filter_by_currency(): void
    {
        Country::factory()->create(['currency_code' => 'EUR']);
        Country::factory()->create(['currency_code' => 'USD']);

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\ListCountries::class)
            ->filterTable('currency_code', 'EUR')
            ->assertCanSeeTableRecords(Country::where('currency_code', 'EUR')->get())
            ->assertCanNotSeeTableRecords(Country::where('currency_code', 'USD')->get());
    }

    public function test_countries_resource_can_search_countries(): void
    {
        Country::factory()->create(['name' => 'Lithuania', 'cca2' => 'LT']);
        Country::factory()->create(['name' => 'Latvia', 'cca2' => 'LV']);

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\ListCountries::class)
            ->searchTable('Lithuania')
            ->assertCanSeeTableRecords(Country::where('name', 'like', '%Lithuania%')->get())
            ->assertCanNotSeeTableRecords(Country::where('name', 'like', '%Latvia%')->get());
    }

    public function test_countries_resource_can_sort_by_name(): void
    {
        Country::factory()->create(['name' => 'Lithuania', 'sort_order' => 2]);
        Country::factory()->create(['name' => 'Latvia', 'sort_order' => 1]);
        Country::factory()->create(['name' => 'Estonia', 'sort_order' => 3]);

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\ListCountries::class)
            ->sortTable('name')
            ->assertCanSeeTableRecords(Country::orderBy('name')->get());
    }

    public function test_countries_resource_can_sort_by_region(): void
    {
        Country::factory()->create(['region' => 'Europe']);
        Country::factory()->create(['region' => 'Asia']);
        Country::factory()->create(['region' => 'Africa']);

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\ListCountries::class)
            ->sortTable('region')
            ->assertCanSeeTableRecords(Country::orderBy('region')->get());
    }

    public function test_countries_resource_can_handle_translations(): void
    {
        $country = Country::factory()->create(['name' => 'Lithuania']);

        $translationData = [
            'translations' => [
                [
                    'locale' => 'lt',
                    'name' => 'Lietuva',
                    'name_official' => 'Lietuvos Respublika',
                    'description' => 'Šalis Europoje',
                ],
                [
                    'locale' => 'en',
                    'name' => 'Lithuania',
                    'name_official' => 'Republic of Lithuania',
                    'description' => 'A country in Europe',
                ],
            ],
        ];

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\EditCountry::class, [
            'record' => $country->getRouteKey(),
        ])
            ->fillForm($translationData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('country_translations', [
            'country_id' => $country->id,
            'locale' => 'lt',
            'name' => 'Lietuva',
            'name_official' => 'Lietuvos Respublika',
            'description' => 'Šalis Europoje',
        ]);

        $this->assertDatabaseHas('country_translations', [
            'country_id' => $country->id,
            'locale' => 'en',
            'name' => 'Lithuania',
            'name_official' => 'Republic of Lithuania',
            'description' => 'A country in Europe',
        ]);
    }

    public function test_countries_resource_can_handle_currencies_array(): void
    {
        $countryData = [
            'name' => 'Lithuania',
            'cca2' => 'LT',
            'cca3' => 'LTU',
            'currencies' => [
                ['key' => 'EUR', 'value' => 'Euro'],
                ['key' => 'USD', 'value' => 'US Dollar'],
            ],
        ];

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\CreateCountry::class)
            ->fillForm($countryData)
            ->call('create')
            ->assertHasNoFormErrors();

        $country = Country::where('cca2', 'LT')->first();
        $this->assertIsArray($country->currencies);
        $this->assertEquals('Euro', $country->currencies['EUR']);
        $this->assertEquals('US Dollar', $country->currencies['USD']);
    }

    public function test_countries_resource_can_handle_languages_array(): void
    {
        $countryData = [
            'name' => 'Lithuania',
            'cca2' => 'LT',
            'cca3' => 'LTU',
            'languages' => [
                ['key' => 'lt', 'value' => 'Lithuanian'],
                ['key' => 'en', 'value' => 'English'],
            ],
        ];

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\CreateCountry::class)
            ->fillForm($countryData)
            ->call('create')
            ->assertHasNoFormErrors();

        $country = Country::where('cca2', 'LT')->first();
        $this->assertIsArray($country->languages);
        $this->assertEquals('Lithuanian', $country->languages['lt']);
        $this->assertEquals('English', $country->languages['en']);
    }

    public function test_countries_resource_can_handle_timezones_array(): void
    {
        $countryData = [
            'name' => 'Lithuania',
            'cca2' => 'LT',
            'cca3' => 'LTU',
            'timezones' => [
                ['key' => 'Europe/Vilnius', 'value' => 'Vilnius Time'],
            ],
        ];

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\CreateCountry::class)
            ->fillForm($countryData)
            ->call('create')
            ->assertHasNoFormErrors();

        $country = Country::where('cca2', 'LT')->first();
        $this->assertIsArray($country->timezones);
        $this->assertEquals('Vilnius Time', $country->timezones['Europe/Vilnius']);
    }

    public function test_countries_resource_can_handle_metadata(): void
    {
        $countryData = [
            'name' => 'Lithuania',
            'cca2' => 'LT',
            'cca3' => 'LTU',
            'metadata' => [
                'population' => '2794324',
                'area' => '65300',
            ],
        ];

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\CreateCountry::class)
            ->fillForm($countryData)
            ->call('create')
            ->assertHasNoFormErrors();

        $country = Country::where('cca2', 'LT')->first();
        $this->assertIsArray($country->metadata);
        $this->assertEquals('2794324', $country->metadata['population']);
        $this->assertEquals('65300', $country->metadata['area']);
    }

    public function test_countries_resource_validation_requires_cca2(): void
    {
        $countryData = [
            'name' => 'Lithuania',
            'cca3' => 'LTU',
        ];

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\CreateCountry::class)
            ->fillForm($countryData)
            ->call('create')
            ->assertHasFormErrors(['cca2']);
    }

    public function test_countries_resource_validation_requires_cca3(): void
    {
        $countryData = [
            'name' => 'Lithuania',
            'cca2' => 'LT',
        ];

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\CreateCountry::class)
            ->fillForm($countryData)
            ->call('create')
            ->assertHasFormErrors(['cca3']);
    }

    public function test_countries_resource_validation_requires_unique_cca2(): void
    {
        Country::factory()->create(['cca2' => 'LT']);

        $countryData = [
            'name' => 'Lithuania',
            'cca2' => 'LT',
            'cca3' => 'LTU',
        ];

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\CreateCountry::class)
            ->fillForm($countryData)
            ->call('create')
            ->assertHasFormErrors(['cca2']);
    }

    public function test_countries_resource_validation_requires_unique_cca3(): void
    {
        Country::factory()->create(['cca3' => 'LTU']);

        $countryData = [
            'name' => 'Lithuania',
            'cca2' => 'LT',
            'cca3' => 'LTU',
        ];

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\CreateCountry::class)
            ->fillForm($countryData)
            ->call('create')
            ->assertHasFormErrors(['cca3']);
    }

    public function test_countries_resource_can_restore_deleted_country(): void
    {
        $country = Country::factory()->create();
        $country->delete();

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\ListCountries::class)
            ->callTableAction('restore', $country);

        $this->assertDatabaseHas('countries', [
            'id' => $country->id,
            'deleted_at' => null,
        ]);
    }

    public function test_countries_resource_can_force_delete_country(): void
    {
        $country = Country::factory()->create();
        $country->delete();

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\ListCountries::class)
            ->callTableAction('forceDelete', $country);

        $this->assertDatabaseMissing('countries', ['id' => $country->id]);
    }
}