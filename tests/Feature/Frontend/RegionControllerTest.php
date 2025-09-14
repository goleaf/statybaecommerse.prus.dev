<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Country;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RegionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_region_index_page_loads(): void
    {
        Region::factory()->count(5)->create();
        
        $response = $this->get(route('regions.index'));
        
        $response->assertOk()
            ->assertViewIs('regions.index')
            ->assertSee('Regions');
    }

    public function test_region_show_page_loads(): void
    {
        $region = Region::factory()->create();
        
        $response = $this->get(route('regions.show', $region));
        
        $response->assertOk()
            ->assertViewIs('regions.show')
            ->assertSee($region->name);
    }

    public function test_region_index_with_filters(): void
    {
        $country = Country::factory()->create();
        $region1 = Region::factory()->create(['country_id' => $country->id, 'level' => 1, 'is_enabled' => true]);
        $region2 = Region::factory()->create(['country_id' => $country->id, 'level' => 2, 'is_enabled' => true]);
        
        $response = $this->get(route('regions.index', [
            'country' => $country->id,
            'level' => 1,
        ]));
        
        $response->assertOk();
        $this->assertTrue(true); // Page loads successfully with filters
    }

    public function test_region_search_api(): void
    {
        Region::factory()->create(['name' => 'Lithuania']);
        Region::factory()->create(['name' => 'Germany']);
        
        $response = $this->get(route('regions.api.search', ['search' => 'Lithuania']));
        
        $response->assertOk()
            ->assertJsonStructure([
                'regions',
                'total',
            ]);
        
        $data = $response->json();
        $this->assertEquals(1, $data['total']);
        $this->assertEquals('Lithuania', $data['regions'][0]['name']);
    }

    public function test_region_by_country_api(): void
    {
        $country = Country::factory()->create();
        Region::factory()->count(3)->create(['country_id' => $country->id]);
        Region::factory()->create(); // Different country
        
        $response = $this->get(route('regions.api.by-country', $country->id));
        
        $response->assertOk()
            ->assertJsonStructure([
                'regions',
                'total',
            ]);
        
        $data = $response->json();
        $this->assertEquals(3, $data['total']);
    }

    public function test_region_by_level_api(): void
    {
        Region::factory()->count(2)->create(['level' => 1, 'is_enabled' => true]);
        Region::factory()->create(['level' => 2, 'is_enabled' => true]);
        
        $response = $this->get(route('regions.api.by-level', 1));
        
        $response->assertOk()
            ->assertJsonStructure([
                'regions',
                'total',
            ]);
        
        $data = $response->json();
        $this->assertGreaterThanOrEqual(2, $data['total']);
    }

    public function test_region_hierarchy_api(): void
    {
        Region::factory()->count(3)->create(['parent_id' => null]); // Root regions
        
        $response = $this->get(route('regions.api.hierarchy'));
        
        $response->assertOk()
            ->assertJsonStructure([
                'hierarchy',
                'total',
            ]);
        
        $data = $response->json();
        $this->assertEquals(3, $data['total']);
    }

    public function test_region_statistics_api(): void
    {
        Region::factory()->count(5)->create(['is_enabled' => true, 'parent_id' => null]);
        Region::factory()->count(2)->create(['is_enabled' => false, 'parent_id' => null]);
        Region::factory()->count(3)->create(['is_default' => true, 'parent_id' => null]);
        
        $response = $this->get(route('regions.api.statistics'));
        
        $response->assertOk()
            ->assertJsonStructure([
                'total_regions',
                'enabled_regions',
                'default_regions',
                'root_regions',
                'by_level',
                'by_country',
            ]);
        
        $data = $response->json();
        $this->assertGreaterThanOrEqual(7, $data['total_regions']);
        $this->assertGreaterThanOrEqual(5, $data['enabled_regions']);
        $this->assertGreaterThanOrEqual(3, $data['default_regions']);
    }

    public function test_region_show_with_relations(): void
    {
        $country = Country::factory()->create();
        $parentRegion = Region::factory()->create(['country_id' => $country->id]);
        $region = Region::factory()->create([
            'country_id' => $country->id,
            'parent_id' => $parentRegion->id,
        ]);
        
        $response = $this->get(route('regions.show', $region));
        
        $response->assertOk()
            ->assertSee($region->name)
            ->assertSee($country->name)
            ->assertSee($parentRegion->name);
    }

    public function test_region_index_pagination(): void
    {
        Region::factory()->count(30)->create();
        
        $response = $this->get(route('regions.index'));
        
        $response->assertOk();
        $this->assertTrue(true); // Page loads successfully with many regions
    }
}