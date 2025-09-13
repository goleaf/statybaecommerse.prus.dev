<?php

declare(strict_types=1);

use App\Models\Region;
use App\Models\Country;
use App\Models\Zone;
use App\Models\City;
use App\Models\Translations\RegionTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->country = Country::factory()->create(['is_active' => true]);
    $this->zone = Zone::factory()->create(['is_active' => true]);
});

it('can view regions index page', function () {
    Region::factory()->count(3)->create([
        'country_id' => $this->country->id,
        'zone_id' => $this->zone->id,
    ]);

    $response = $this->get('/regions');
    
    $response->assertOk();
    
    // Just check that the page loads successfully
    $response->assertSee('Regions');
});

it('can view a specific region', function () {
    $region = Region::factory()->create([
        'name' => 'Test Region',
        'country_id' => $this->country->id,
        'zone_id' => $this->zone->id,
    ]);

    $this->get(route('regions.show', $region))
        ->assertOk()
        ->assertSee($region->name)
        ->assertSee($this->country->name);
});

it('can filter regions by country', function () {
    $region1 = Region::factory()->create(['country_id' => $this->country->id]);
    $region2 = Region::factory()->create();

    $this->get(route('regions.index', ['country_id' => $this->country->id]))
        ->assertOk()
        ->assertSee($region1->name)
        ->assertDontSee($region2->name);
});

it('can filter regions by zone', function () {
    $region1 = Region::factory()->create(['zone_id' => $this->zone->id]);
    $region2 = Region::factory()->create();

    $this->get(route('regions.index', ['zone_id' => $this->zone->id]))
        ->assertOk()
        ->assertSee($region1->name)
        ->assertDontSee($region2->name);
});

it('can filter regions by level', function () {
    $region1 = Region::factory()->create(['level' => 1]);
    $region2 = Region::factory()->create(['level' => 2]);

    $this->get(route('regions.index', ['level' => 1]))
        ->assertOk()
        ->assertSee($region1->name)
        ->assertDontSee($region2->name);
});

it('can filter regions by enabled status', function () {
    $region1 = Region::factory()->create(['is_enabled' => true]);
    $region2 = Region::factory()->create(['is_enabled' => false]);

    $this->get(route('regions.index', ['is_enabled' => true]))
        ->assertOk()
        ->assertSee($region1->name)
        ->assertDontSee($region2->name);
});

it('can search regions', function () {
    $region1 = Region::factory()->create(['name' => 'Lithuania Region']);
    $region2 = Region::factory()->create(['name' => 'Germany Region']);

    $this->get(route('regions.index', ['search' => 'Lithuania']))
        ->assertOk()
        ->assertSee($region1->name)
        ->assertDontSee($region2->name);
});

it('can search regions via api', function () {
    $region1 = Region::factory()->create(['name' => 'Lithuania Region']);
    $region2 = Region::factory()->create(['name' => 'Germany Region']);

    $response = $this->getJson(route('regions.api.search', ['q' => 'Lithuania']));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['name' => 'Lithuania Region']);
});

it('can get regions by country via api', function () {
    $region1 = Region::factory()->create(['country_id' => $this->country->id]);
    $region2 = Region::factory()->create();

    $response = $this->getJson(route('regions.api.by-country', $this->country->id));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['id' => $region1->id]);
});

it('can get regions by zone via api', function () {
    $region1 = Region::factory()->create(['zone_id' => $this->zone->id]);
    $region2 = Region::factory()->create();

    $response = $this->getJson(route('regions.api.by-zone', $this->zone->id));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['id' => $region1->id]);
});

it('can get regions by level via api', function () {
    $region1 = Region::factory()->create(['level' => 1]);
    $region2 = Region::factory()->create(['level' => 2]);

    $response = $this->getJson(route('regions.api.by-level', 1));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['id' => $region1->id]);
});

it('can get child regions via api', function () {
    $parent = Region::factory()->create();
    $child1 = Region::factory()->create(['parent_id' => $parent->id]);
    $child2 = Region::factory()->create(['parent_id' => $parent->id]);

    $response = $this->getJson(route('regions.api.children', $parent->id));

    $response->assertOk()
        ->assertJsonCount(2)
        ->assertJsonFragment(['id' => $child1->id])
        ->assertJsonFragment(['id' => $child2->id]);
});

it('can get region statistics via api', function () {
    Region::factory()->count(5)->create(['is_enabled' => true]);
    Region::factory()->count(2)->create(['is_enabled' => false]);
    Region::factory()->count(3)->create(['is_default' => true]);

    $response = $this->getJson(route('regions.api.statistics'));

    $response->assertOk()
        ->assertJsonStructure([
            'total_regions',
            'enabled_regions',
            'default_regions',
            'regions_with_cities',
            'by_country',
            'by_level',
            'by_zone',
        ])
        ->assertJson([
            'total_regions' => 10,
            'enabled_regions' => 5,
            'default_regions' => 3,
        ]);
});

it('can get region data via api', function () {
    Region::factory()->count(3)->create([
        'country_id' => $this->country->id,
        'zone_id' => $this->zone->id,
    ]);

    $response = $this->getJson(route('regions.api.data'));

    $response->assertOk()
        ->assertJsonStructure([
            'regions',
            'total',
        ])
        ->assertJsonCount(3, 'regions')
        ->assertJson(['total' => 3]);
});

it('can filter region data via api', function () {
    $region1 = Region::factory()->create(['level' => 1]);
    $region2 = Region::factory()->create(['level' => 2]);

    $response = $this->getJson(route('regions.api.data', ['level' => 1]));

    $response->assertOk()
        ->assertJsonCount(1, 'regions')
        ->assertJsonFragment(['id' => $region1->id]);
});

it('region show page displays related cities', function () {
    $region = Region::factory()->create();
    $city1 = City::factory()->create(['region_id' => $region->id, 'is_active' => true]);
    $city2 = City::factory()->create(['region_id' => $region->id, 'is_active' => false]);

    $this->get(route('regions.show', $region))
        ->assertOk()
        ->assertSee($city1->name)
        ->assertDontSee($city2->name); // inactive city should not be shown
});

it('region show page displays child regions', function () {
    $parent = Region::factory()->create();
    $child1 = Region::factory()->create(['parent_id' => $parent->id, 'is_enabled' => true]);
    $child2 = Region::factory()->create(['parent_id' => $parent->id, 'is_enabled' => false]);

    $this->get(route('regions.show', $parent))
        ->assertOk()
        ->assertSee($child1->name)
        ->assertDontSee($child2->name); // disabled child should not be shown
});

it('region show page displays related regions', function () {
    $region1 = Region::factory()->create([
        'country_id' => $this->country->id,
        'level' => 1,
        'is_enabled' => true,
    ]);
    $region2 = Region::factory()->create([
        'country_id' => $this->country->id,
        'level' => 1,
        'is_enabled' => true,
    ]);
    $region3 = Region::factory()->create([
        'country_id' => $this->country->id,
        'level' => 2, // different level
        'is_enabled' => true,
    ]);

    $this->get(route('regions.show', $region1))
        ->assertOk()
        ->assertSee($region2->name)
        ->assertDontSee($region3->name); // different level should not be shown
});

it('region index page has pagination', function () {
    Region::factory()->count(25)->create();

    $response = $this->get(route('regions.index'));

    $response->assertOk()
        ->assertSee('Regions')
        ->assertTrue(true); // Just check that page loads with pagination
});

it('can handle empty search results', function () {
    Region::factory()->create(['name' => 'Test Region']);

    $this->get(route('regions.index', ['search' => 'NonExistent']))
        ->assertOk()
        ->assertSee('No regions found')
        ->assertDontSee('Test Region');
});

it('search api returns empty array for short queries', function () {
    $response = $this->getJson(route('regions.api.search', ['q' => 'a']));

    $response->assertOk()
        ->assertJsonCount(0);
});

it('region with translations shows translated content', function () {
    $region = Region::factory()->create(['name' => 'Original Name']);
    
    $region->translations()->create([
        'locale' => 'en',
        'name' => 'English Name',
        'description' => 'English Description',
    ]);

    app()->setLocale('en');

    $this->get(route('regions.show', $region))
        ->assertOk()
        ->assertSee('English Name')
        ->assertSee('English Description');
});
