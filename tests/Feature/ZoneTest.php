<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Zone;
use App\Models\Currency;
use App\Models\Country;
use App\Models\Translations\ZoneTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ZoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_zone(): void
    {
        $currency = Currency::factory()->create();
        
        $zoneData = [
            'name' => 'Test Zone',
            'slug' => 'test-zone',
            'code' => 'TZ',
            'description' => 'Test zone description',
            'currency_id' => $currency->id,
            'tax_rate' => 21.00,
            'shipping_rate' => 5.99,
            'type' => 'shipping',
            'priority' => 1,
            'is_enabled' => true,
            'is_active' => true,
            'is_default' => false,
        ];

        $zone = Zone::create($zoneData);

        $this->assertDatabaseHas('zones', [
            'name' => 'Test Zone',
            'code' => 'TZ',
            'currency_id' => $currency->id,
        ]);

        $this->assertEquals('Test Zone', $zone->name);
        $this->assertEquals('TZ', $zone->code);
        $this->assertEquals(21.00, $zone->tax_rate);
        $this->assertEquals(5.99, $zone->shipping_rate);
    }

    public function test_zone_can_have_translations(): void
    {
        $zone = Zone::factory()->create();
        
        $translation = ZoneTranslation::create([
            'zone_id' => $zone->id,
            'locale' => 'lt',
            'name' => 'Testinė zona',
            'description' => 'Testinės zonos aprašymas',
        ]);

        $this->assertDatabaseHas('zone_translations', [
            'zone_id' => $zone->id,
            'locale' => 'lt',
            'name' => 'Testinė zona',
        ]);

        $this->assertEquals('Testinė zona', $zone->trans('name', 'lt'));
        $this->assertEquals('Testinės zonos aprašymas', $zone->trans('description', 'lt'));
    }

    public function test_zone_can_have_countries(): void
    {
        $zone = Zone::factory()->create();
        $country1 = Country::factory()->create();
        $country2 = Country::factory()->create();

        $zone->countries()->attach([$country1->id, $country2->id]);

        $this->assertCount(2, $zone->countries);
        $this->assertTrue($zone->countries->contains($country1));
        $this->assertTrue($zone->countries->contains($country2));
    }

    public function test_zone_calculates_tax_correctly(): void
    {
        $zone = Zone::factory()->create(['tax_rate' => 21.00]);
        
        $taxAmount = $zone->calculateTax(100.00);
        
        $this->assertEquals(21.00, $taxAmount);
    }

    public function test_zone_calculates_shipping_correctly(): void
    {
        $zone = Zone::factory()->create(['shipping_rate' => 5.99]);
        
        $shippingCost = $zone->calculateShipping(2.0, 50.00);
        
        $this->assertEquals(11.98, $shippingCost); // 5.99 * 2.0
    }

    public function test_zone_free_shipping_threshold(): void
    {
        $zone = Zone::factory()->create([
            'shipping_rate' => 5.99,
            'free_shipping_threshold' => 100.00,
        ]);
        
        // Order below threshold
        $shippingCost = $zone->calculateShipping(1.0, 50.00);
        $this->assertEquals(5.99, $shippingCost);
        
        // Order above threshold
        $shippingCost = $zone->calculateShipping(1.0, 150.00);
        $this->assertEquals(0, $shippingCost);
    }

    public function test_zone_order_amount_limits(): void
    {
        $zone = Zone::factory()->create([
            'min_order_amount' => 20.00,
            'max_order_amount' => 500.00,
        ]);
        
        // Order below minimum
        $this->assertFalse($zone->isEligibleForOrder(10.00));
        
        // Order within limits
        $this->assertTrue($zone->isEligibleForOrder(100.00));
        
        // Order above maximum
        $this->assertFalse($zone->isEligibleForOrder(600.00));
    }

    public function test_zone_scopes(): void
    {
        Zone::factory()->create(['is_enabled' => true, 'is_active' => true]);
        Zone::factory()->create(['is_enabled' => false, 'is_active' => true]);
        Zone::factory()->create(['is_enabled' => true, 'is_active' => false]);
        Zone::factory()->create(['is_default' => true]);

        $this->assertCount(1, Zone::enabled()->get());
        $this->assertCount(2, Zone::active()->get());
        $this->assertCount(1, Zone::default()->get());
    }

    public function test_zone_static_methods(): void
    {
        $defaultZone = Zone::factory()->create(['is_default' => true]);
        $activeZone = Zone::factory()->create(['is_active' => true, 'is_enabled' => true]);
        
        $this->assertEquals($defaultZone->id, Zone::getDefaultZone()->id);
        $this->assertTrue(Zone::getActiveZones()->contains($activeZone));
    }

    public function test_zone_formatted_attributes(): void
    {
        $zone = Zone::factory()->create([
            'tax_rate' => 21.00,
            'shipping_rate' => 5.99,
            'min_order_amount' => 20.00,
            'max_order_amount' => 500.00,
            'free_shipping_threshold' => 100.00,
        ]);
        
        $this->assertEquals('21.00%', $zone->formatted_tax_rate);
        $this->assertEquals('€5.99', $zone->formatted_shipping_rate);
        $this->assertEquals('€20.00', $zone->formatted_min_order_amount);
        $this->assertEquals('€500.00', $zone->formatted_max_order_amount);
        $this->assertEquals('€100.00', $zone->formatted_free_shipping_threshold);
    }
}

