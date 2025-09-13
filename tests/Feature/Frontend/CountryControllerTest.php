<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CountryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_country_index_page_loads(): void
    {
        Country::factory()->count(5)->create();

        $response = $this->get(route('countries.index'));
        $response->assertOk()
            ->assertViewIs('countries.index')
            ->assertSee('Countries');
    }

    public function test_country_show_page_loads(): void
    {
        $country = Country::factory()->create([
            'name' => 'Lithuania',
            'cca2' => 'LT',
            'region' => 'Europe',
        ]);

        $response = $this->get(route('countries.show', $country));
        $response->assertOk()
            ->assertViewIs('countries.show')
            ->assertSee('Lithuania')
            ->assertSee('LT');
    }

    public function test_country_index_with_filters(): void
    {
        Country::factory()->create(['region' => 'Europe', 'is_eu_member' => true]);
        Country::factory()->create(['region' => 'Asia', 'is_eu_member' => false]);

        // Test region filter
        $response = $this->get(route('countries.index', ['region' => 'Europe']));
        $response->assertOk()
            ->assertSee('Europe');

        // Test EU member filter
        $response = $this->get(route('countries.index', ['is_eu_member' => '1']));
        $response->assertOk();

        // Test VAT filter
        $response = $this->get(route('countries.index', ['requires_vat' => '1']));
        $response->assertOk();
    }

    public function test_country_index_with_search(): void
    {
        Country::factory()->create(['name' => 'Germany']);
        Country::factory()->create(['name' => 'France']);

        $response = $this->get(route('countries.index', ['search' => 'Germany']));
        $response->assertOk()
            ->assertSee('Germany')
            ->assertDontSee('France');
    }

    public function test_country_search_api(): void
    {
        Country::factory()->create(['name' => 'Spain', 'cca2' => 'ES']);

        $response = $this->get(route('countries.api.search', ['q' => 'Spain']));
        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['name' => 'Spain']);

        // Test minimum query length
        $response = $this->get(route('countries.api.search', ['q' => 'S']));
        $response->assertOk()
            ->assertJsonCount(0);
    }

    public function test_country_by_region_api(): void
    {
        Country::factory()->create(['region' => 'Europe', 'name' => 'Germany']);
        Country::factory()->create(['region' => 'Asia', 'name' => 'Japan']);

        $response = $this->get(route('countries.api.by-region', 'Europe'));
        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['name' => 'Germany']);
    }

    public function test_country_eu_members_api(): void
    {
        Country::factory()->create(['is_eu_member' => true, 'name' => 'Germany']);
        Country::factory()->create(['is_eu_member' => false, 'name' => 'USA']);

        $response = $this->get(route('countries.api.eu-members'));
        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['name' => 'Germany']);
    }

    public function test_country_with_vat_api(): void
    {
        Country::factory()->create(['requires_vat' => true, 'name' => 'Germany']);
        Country::factory()->create(['requires_vat' => false, 'name' => 'USA']);

        $response = $this->get(route('countries.api.with-vat'));
        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['name' => 'Germany']);
    }

    public function test_country_statistics_api(): void
    {
        Country::factory()->create(['is_active' => true, 'is_eu_member' => true, 'requires_vat' => true]);
        Country::factory()->create(['is_active' => false, 'is_eu_member' => false, 'requires_vat' => false]);

        $response = $this->get(route('countries.api.statistics'));
        $response->assertOk()
            ->assertJsonStructure([
                'total_countries',
                'active_countries',
                'eu_members',
                'countries_with_vat',
                'by_region',
                'by_currency',
            ]);
    }

    public function test_country_show_with_relations(): void
    {
        $country = Country::factory()->create();
        
        // Create related data
        $region = \App\Models\Region::factory()->create(['country_id' => $country->id]);
        $city = \App\Models\City::factory()->create(['country_id' => $country->id]);
        $address = \App\Models\Address::factory()->create(['country_code' => $country->cca2]);

        $response = $this->get(route('countries.show', $country));
        $response->assertOk()
            ->assertSee($region->name)
            ->assertSee($city->name);
    }

    public function test_country_index_pagination(): void
    {
        Country::factory()->count(15)->create();

        $response = $this->get(route('countries.index'));
        $response->assertOk();
        
        // Check if pagination is present
        $response->assertSee('pagination');
    }

    public function test_country_show_empty_state(): void
    {
        $country = Country::factory()->create([
            'name' => 'Empty Country',
            'description' => null,
        ]);

        $response = $this->get(route('countries.show', $country));
        $response->assertOk()
            ->assertSee('Empty Country');
    }

    public function test_country_search_case_insensitive(): void
    {
        Country::factory()->create(['name' => 'Germany']);

        $response = $this->get(route('countries.api.search', ['q' => 'germany']));
        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['name' => 'Germany']);
    }

    public function test_country_search_multiple_fields(): void
    {
        Country::factory()->create([
            'name' => 'United States',
            'cca2' => 'US',
            'cca3' => 'USA',
        ]);

        // Search by name
        $response = $this->get(route('countries.api.search', ['q' => 'United']));
        $response->assertOk()
            ->assertJsonCount(1);

        // Search by code
        $response = $this->get(route('countries.api.search', ['q' => 'US']));
        $response->assertOk()
            ->assertJsonCount(1);
    }

    public function test_country_statistics_accuracy(): void
    {
        // Create test data
        Country::factory()->create(['is_active' => true, 'is_eu_member' => true, 'requires_vat' => true, 'region' => 'Europe']);
        Country::factory()->create(['is_active' => false, 'is_eu_member' => false, 'requires_vat' => false, 'region' => 'Asia']);
        Country::factory()->create(['is_active' => true, 'is_eu_member' => true, 'requires_vat' => false, 'region' => 'Europe']);

        $response = $this->get(route('countries.api.statistics'));
        $response->assertOk();
        
        $data = $response->json();
        
        $this->assertEquals(3, $data['total_countries']);
        $this->assertEquals(2, $data['active_countries']);
        $this->assertEquals(2, $data['eu_members']);
        $this->assertEquals(1, $data['countries_with_vat']);
    }
}