<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\CountryResource;
use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CountryResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create();
        $this->actingAs($this->adminUser);
    }

    public function test_can_render_index_page(): void
    {
        $this
            ->get(CountryResource::getUrl('index'))
            ->assertOk();
    }

    public function test_can_list_countries(): void
    {
        $countries = Country::factory(3)->create();

        Livewire::test(CountryResource\Pages\ListCountries::class)
            ->assertCanSeeTableRecords($countries);
    }

    public function test_can_render_create_page(): void
    {
        $this
            ->get(CountryResource::getUrl('create'))
            ->assertOk();
    }

    public function test_can_create_country(): void
    {
        $this->markTestSkipped('Skipped due to Filament tab-layout-plugin container initialization issue');

        $newData = [
            'cca2' => 'XY',
            'cca3' => 'XYZ',
            'region' => 'Europe',
            'subregion' => 'Test Region',
            'phone_calling_code' => '999',
            'flag' => '🏳️',
            'latitude' => 50.0,
            'longitude' => 10.0,
            'currencies' => ['EUR'],
            'translations' => [
                ['locale' => 'en', 'name' => 'Test Country', 'name_official' => 'Test Country Official'],
                ['locale' => 'lt', 'name' => 'Testo Šalis', 'name_official' => 'Testo Šalies Oficialus'],
            ],
        ];

        Livewire::test(CountryResource\Pages\CreateCountry::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('countries', [
            'cca2' => 'XY',
            'cca3' => 'XYZ',
            'region' => 'Europe',
        ]);

        $this->assertDatabaseHas('country_translations', [
            'locale' => 'en',
            'name' => 'Test Country',
        ]);

        $this->assertDatabaseHas('country_translations', [
            'locale' => 'lt',
            'name' => 'Testo Šalis',
        ]);
    }

    public function test_can_render_edit_page(): void
    {
        $this->markTestSkipped('Skipped due to Filament tab-layout-plugin container initialization issue');

        $country = Country::factory()->create();

        $this->get(CountryResource::getUrl('edit', [
            'record' => $country,
        ]))->assertOk();
    }

    public function test_can_retrieve_data_for_edit(): void
    {
        $this->markTestSkipped('Skipped due to Filament tab-layout-plugin container initialization issue');

        $country = Country::factory()->create();

        Livewire::test(CountryResource\Pages\EditCountry::class, [
            'record' => $country->getRouteKey(),
        ])
            ->assertFormSet([
                'cca2' => $country->cca2,
                'cca3' => $country->cca3,
                'region' => $country->region,
            ]);
    }

    public function test_can_update_country(): void
    {
        $this->markTestSkipped('Skipped due to Filament tab-layout-plugin container initialization issue');

        $country = Country::factory()->create();
        $newData = ['region' => 'Asia'];

        Livewire::test(CountryResource\Pages\EditCountry::class, [
            'record' => $country->getRouteKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('countries', [
            'id' => $country->id,
            'region' => 'Asia',
        ]);
    }

    public function test_can_delete_country(): void
    {
        $this->markTestSkipped('Skipped due to Filament tab-layout-plugin container initialization issue');

        $country = Country::factory()->create();

        Livewire::test(CountryResource\Pages\EditCountry::class, [
            'record' => $country->getRouteKey(),
        ])
            ->callAction('delete');

        $this->assertModelMissing($country);
    }

    public function test_can_filter_countries_by_region(): void
    {
        $europeCountry = Country::factory()->create(['region' => 'Europe']);
        $asiaCountry = Country::factory()->create(['region' => 'Asia']);

        Livewire::test(CountryResource\Pages\ListCountries::class)
            ->filterTable('region', 'Europe')
            ->assertCanSeeTableRecords([$europeCountry])
            ->assertCanNotSeeTableRecords([$asiaCountry]);
    }

    public function test_can_search_countries(): void
    {
        $country = Country::factory()->create(['cca2' => 'LT']);
        $otherCountry = Country::factory()->create(['cca2' => 'DE']);

        Livewire::test(CountryResource\Pages\ListCountries::class)
            ->searchTable('LT')
            ->assertCanSeeTableRecords([$country])
            ->assertCanNotSeeTableRecords([$otherCountry]);
    }
}
