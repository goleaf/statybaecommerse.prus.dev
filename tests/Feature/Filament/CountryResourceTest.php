<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

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

        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_country_resource_can_render_list_page(): void
    {
        Country::factory()->count(5)->create();

        $this->get(route('filament.admin.resources.countries.index'))
            ->assertOk();
    }

    public function test_country_resource_can_render_create_page(): void
    {
        $this->get(route('filament.admin.resources.countries.create'))
            ->assertOk();
    }

    public function test_country_resource_can_create_country(): void
    {
        $countryData = [
            'name' => 'Test Country',
            'name_official' => 'Test Country Official',
            'cca2' => 'TC',
            'cca3' => 'TCT',
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
            'name' => 'Test Country',
            'cca2' => 'TC',
            'cca3' => 'TCT',
        ]);
    }

    public function test_country_resource_can_render_edit_page(): void
    {
        $country = Country::factory()->create();

        $this->get(route('filament.admin.resources.countries.edit', $country))
            ->assertOk();
    }

    public function test_country_resource_can_edit_country(): void
    {
        $country = Country::factory()->create([
            'name' => 'Original Name',
            'cca2' => 'ON',
        ]);

        $updatedData = [
            'name' => 'Updated Name',
            'cca2' => 'UN',
        ];

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\EditCountry::class, [
            'record' => $country->getRouteKey(),
        ])
            ->fillForm($updatedData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('countries', [
            'id' => $country->id,
            'name' => 'Updated Name',
            'cca2' => 'UN',
        ]);
    }

    public function test_country_resource_can_render_view_page(): void
    {
        $country = Country::factory()->create();

        $this->get(route('filament.admin.resources.countries.view', $country))
            ->assertOk();
    }

    public function test_country_resource_can_delete_country(): void
    {
        $country = Country::factory()->create();

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\ListCountries::class)
            ->callTableAction('delete', $country);

        $this->assertSoftDeleted('countries', [
            'id' => $country->id,
        ]);
    }

    public function test_country_resource_table_filters_work(): void
    {
        Country::factory()->create(['is_active' => true, 'is_eu_member' => true]);
        Country::factory()->create(['is_active' => false, 'is_eu_member' => false]);

        // Test active filter
        Livewire::test(\App\Filament\Resources\CountryResource\Pages\ListCountries::class)
            ->filterTable('is_active', '1')
            ->assertCanSeeTableRecords(Country::where('is_active', true)->get());

        // Test EU member filter
        Livewire::test(\App\Filament\Resources\CountryResource\Pages\ListCountries::class)
            ->filterTable('is_eu_member', '1')
            ->assertCanSeeTableRecords(Country::where('is_eu_member', true)->get());
    }

    public function test_country_resource_table_search_works(): void
    {
        Country::factory()->create(['name' => 'Lithuania']);
        Country::factory()->create(['name' => 'Germany']);

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\ListCountries::class)
            ->searchTable('Lithuania')
            ->assertCanSeeTableRecords(Country::where('name', 'Lithuania')->get())
            ->assertCanNotSeeTableRecords(Country::where('name', 'Germany')->get());
    }

    public function test_country_resource_bulk_actions_work(): void
    {
        $countries = Country::factory()->count(3)->create(['is_active' => false]);

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\ListCountries::class)
            ->callTableBulkAction('activate', $countries)
            ->assertHasNoTableBulkActionErrors();

        foreach ($countries as $country) {
            $this->assertDatabaseHas('countries', [
                'id' => $country->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_country_resource_widgets_load(): void
    {
        Country::factory()->count(10)->create();

        $this->get(route('filament.admin.resources.countries.index'))
            ->assertOk()
            ->assertSee('Total Countries')
            ->assertSee('Active Countries');
    }

    public function test_country_resource_validation_works(): void
    {
        // Test required fields
        Livewire::test(\App\Filament\Resources\CountryResource\Pages\CreateCountry::class)
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors(['name', 'cca2', 'cca3']);

        // Test unique constraints
        Country::factory()->create(['cca2' => 'LT', 'cca3' => 'LTU']);

        Livewire::test(\App\Filament\Resources\CountryResource\Pages\CreateCountry::class)
            ->fillForm([
                'name' => 'Lithuania',
                'cca2' => 'LT', // Already exists
                'cca3' => 'LTU', // Already exists
            ])
            ->call('create')
            ->assertHasFormErrors(['cca2', 'cca3']);
    }

    public function test_country_resource_translation_management(): void
    {
        $country = Country::factory()->create([
            'name' => 'France',
            'description' => 'A country in Europe',
        ]);

        // Test adding translation
        Livewire::test(\App\Filament\Resources\CountryResource\Pages\EditCountry::class, [
            'record' => $country->getRouteKey(),
        ])
            ->fillForm([
                'translations' => [
                    [
                        'locale' => 'fr',
                        'name' => 'France',
                        'name_official' => 'République française',
                        'description' => 'Un pays en Europe',
                    ],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('country_translations', [
            'country_id' => $country->id,
            'locale' => 'fr',
            'name' => 'France',
            'name_official' => 'République française',
        ]);
    }

    public function test_country_resource_global_search(): void
    {
        Country::factory()->create(['name' => 'Spain', 'cca2' => 'ES']);
        Country::factory()->create(['name' => 'Portugal', 'cca2' => 'PT']);

        // Test global search functionality
        $this->get(route('filament.admin.resources.countries.index') . '?search=Spain')
            ->assertOk()
            ->assertSee('Spain')
            ->assertDontSee('Portugal');
    }
}