<?php declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Zone;
use App\Models\Currency;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ZoneControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_zones_index_page_loads(): void
    {
        Zone::factory()->count(3)->create(['is_active' => true, 'is_enabled' => true]);
        
        $response = $this->get(route('zones.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('frontend.zones.index');
        $response->assertViewHas('zones');
    }

    public function test_zone_show_page_loads(): void
    {
        $zone = Zone::factory()->create(['is_active' => true, 'is_enabled' => true]);
        
        $response = $this->get(route('zones.show', $zone));
        
        $response->assertStatus(200);
        $response->assertViewIs('frontend.zones.show');
        $response->assertViewHas('zone', $zone);
    }

    public function test_get_zones_by_country(): void
    {
        $country = Country::factory()->create();
        $zone = Zone::factory()->create(['is_active' => true, 'is_enabled' => true]);
        $zone->countries()->attach($country);
        
        $response = $this->getJson(route('api.zones.by-country', ['country_id' => $country->id]));
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'zones' => [
                '*' => [
                    'id',
                    'name',
                    'code',
                    'type',
                    'tax_rate',
                    'shipping_rate',
                    'free_shipping_threshold',
                    'currency',
                ]
            ]
        ]);
    }

    public function test_calculate_shipping(): void
    {
        $zone = Zone::factory()->create([
            'tax_rate' => 21.00,
            'shipping_rate' => 5.99,
        ]);
        
        $response = $this->postJson(route('zones.calculate-shipping', $zone), [
            'zone_id' => $zone->id,
            'order_amount' => 100.00,
            'weight' => 2.0,
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'shipping_cost',
            'tax_amount',
            'has_free_shipping',
            'total_with_tax',
            'total_with_shipping',
            'currency',
        ]);
        
        $this->assertEquals(11.98, $response->json('shipping_cost')); // 5.99 * 2.0
        $this->assertEquals(21.00, $response->json('tax_amount')); // 100 * 0.21
    }

    public function test_calculate_shipping_with_free_shipping_threshold(): void
    {
        $zone = Zone::factory()->create([
            'shipping_rate' => 5.99,
            'free_shipping_threshold' => 100.00,
        ]);
        
        $response = $this->postJson(route('zones.calculate-shipping', $zone), [
            'zone_id' => $zone->id,
            'order_amount' => 150.00,
            'weight' => 2.0,
        ]);
        
        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('shipping_cost'));
        $this->assertTrue($response->json('has_free_shipping'));
    }

    public function test_get_default_zone(): void
    {
        $defaultZone = Zone::factory()->create(['is_default' => true]);
        
        $response = $this->getJson(route('api.zones.default'));
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'zone' => [
                'id',
                'name',
                'code',
                'type',
                'tax_rate',
                'shipping_rate',
                'free_shipping_threshold',
                'currency',
            ]
        ]);
        
        $this->assertEquals($defaultZone->id, $response->json('zone.id'));
    }

    public function test_calculate_shipping_validation(): void
    {
        $zone = Zone::factory()->create();
        
        $response = $this->postJson(route('zones.calculate-shipping', $zone), [
            'order_amount' => -10.00, // Invalid negative amount
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['order_amount']);
    }

    public function test_zones_index_only_shows_active_enabled_zones(): void
    {
        Zone::factory()->create(['is_active' => true, 'is_enabled' => true]);
        Zone::factory()->create(['is_active' => false, 'is_enabled' => true]);
        Zone::factory()->create(['is_active' => true, 'is_enabled' => false]);
        
        $response = $this->get(route('zones.index'));
        
        $response->assertStatus(200);
        $zones = $response->viewData('zones');
        $this->assertCount(1, $zones->items());
    }
}
