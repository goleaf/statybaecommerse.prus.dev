<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\CampaignConversion;
use App\Models\Campaign;
use App\Models\Order;
use App\Models\User;
use App\Models\CampaignConversionTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_conversion_can_be_created(): void
    {
        $campaign = Campaign::factory()->create();
        $customer = User::factory()->create();
        $order = Order::factory()->create([
            'channel_id' => null,
            'zone_id' => null,
            'partner_id' => null,
        ]);

        $conversion = CampaignConversion::factory()->create([
            'campaign_id' => $campaign->id,
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'conversion_type' => 'purchase',
            'conversion_value' => 150.50,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('campaign_conversions', [
            'id' => $conversion->id,
            'campaign_id' => $campaign->id,
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'conversion_type' => 'purchase',
            'conversion_value' => 150.50,
            'status' => 'completed',
        ]);
    }

    public function test_campaign_conversion_belongs_to_campaign(): void
    {
        $campaign = Campaign::factory()->create();
        $conversion = CampaignConversion::factory()->create(['campaign_id' => $campaign->id]);

        $this->assertInstanceOf(Campaign::class, $conversion->campaign);
        $this->assertEquals($campaign->id, $conversion->campaign->id);
    }

    public function test_campaign_conversion_belongs_to_customer(): void
    {
        $customer = User::factory()->create();
        $conversion = CampaignConversion::factory()->create(['customer_id' => $customer->id]);

        $this->assertInstanceOf(User::class, $conversion->customer);
        $this->assertEquals($customer->id, $conversion->customer->id);
    }

    public function test_campaign_conversion_belongs_to_order(): void
    {
        $order = Order::factory()->create([
            'channel_id' => null,
            'zone_id' => null,
            'partner_id' => null,
        ]);
        $conversion = CampaignConversion::factory()->create(['order_id' => $order->id]);

        $this->assertInstanceOf(Order::class, $conversion->order);
        $this->assertEquals($order->id, $conversion->order->id);
    }

    public function test_campaign_conversion_has_translations(): void
    {
        $conversion = CampaignConversion::factory()->create();
        
        $translation = CampaignConversionTranslation::create([
            'campaign_conversion_id' => $conversion->id,
            'locale' => 'lt',
            'notes' => 'Test pastabos',
        ]);

        $this->assertInstanceOf(CampaignConversionTranslation::class, $conversion->translations->first());
        $this->assertEquals('Test pastabos', $conversion->translations->first()->notes);
    }

    public function test_campaign_conversion_can_get_translation(): void
    {
        $conversion = CampaignConversion::factory()->create();
        
        $ltTranslation = CampaignConversionTranslation::create([
            'campaign_conversion_id' => $conversion->id,
            'locale' => 'lt',
            'notes' => 'Lietuviškos pastabos',
        ]);

        $enTranslation = CampaignConversionTranslation::create([
            'campaign_conversion_id' => $conversion->id,
            'locale' => 'en',
            'notes' => 'English notes',
        ]);

        $this->assertEquals('Lietuviškos pastabos', $conversion->trans('notes', 'lt'));
        $this->assertEquals('English notes', $conversion->trans('notes', 'en'));
    }

    public function test_campaign_conversion_scopes_work(): void
    {
        $campaign = Campaign::factory()->create();
        
        CampaignConversion::factory()->create([
            'campaign_id' => $campaign->id,
            'conversion_type' => 'purchase',
            'status' => 'completed',
            'conversion_value' => 200,
            'device_type' => 'mobile',
            'source' => 'google',
            'medium' => 'cpc',
        ]);

        CampaignConversion::factory()->create([
            'campaign_id' => $campaign->id,
            'conversion_type' => 'signup',
            'status' => 'pending',
            'conversion_value' => 50,
            'device_type' => 'desktop',
            'source' => 'facebook',
            'medium' => 'social',
        ]);

        // Test byCampaign scope
        $this->assertEquals(2, CampaignConversion::byCampaign($campaign->id)->count());

        // Test byType scope
        $this->assertEquals(1, CampaignConversion::byType('purchase')->count());
        $this->assertEquals(1, CampaignConversion::byType('signup')->count());

        // Test byStatus scope
        $this->assertEquals(1, CampaignConversion::byStatus('completed')->count());
        $this->assertEquals(1, CampaignConversion::byStatus('pending')->count());

        // Test byDeviceType scope
        $this->assertEquals(1, CampaignConversion::byDeviceType('mobile')->count());
        $this->assertEquals(1, CampaignConversion::byDeviceType('desktop')->count());

        // Test bySource scope
        $this->assertEquals(1, CampaignConversion::bySource('google')->count());
        $this->assertEquals(1, CampaignConversion::bySource('facebook')->count());

        // Test byMedium scope
        $this->assertEquals(1, CampaignConversion::byMedium('cpc')->count());
        $this->assertEquals(1, CampaignConversion::byMedium('social')->count());

        // Test highValue scope
        $this->assertEquals(1, CampaignConversion::highValue(100)->count());
        $this->assertEquals(2, CampaignConversion::highValue(30)->count());
    }

    public function test_campaign_conversion_accessors_work(): void
    {
        $conversion = CampaignConversion::factory()->create([
            'conversion_value' => 150.75,
            'roi' => 0.25,
            'conversion_rate' => 0.05,
            'device_type' => 'mobile',
            'conversion_type' => 'purchase',
            'status' => 'completed',
        ]);

        $this->assertEquals('€150.75', $conversion->formatted_conversion_value);
        $this->assertEquals('25.00%', $conversion->formatted_roi);
        $this->assertEquals('5.00%', $conversion->formatted_conversion_rate);
        $this->assertEquals(__('campaign_conversions.device_types.mobile'), $conversion->device_type_display);
        $this->assertEquals(__('campaign_conversions.conversion_types.purchase'), $conversion->conversion_type_display);
        $this->assertEquals(__('campaign_conversions.statuses.completed'), $conversion->status_display);
    }

    public function test_campaign_conversion_calculate_roi(): void
    {
        $conversion = CampaignConversion::factory()->create(['conversion_value' => 100]);

        $this->assertEquals(1.0, $conversion->calculateRoi(50)); // (100-50)/50 = 1.0
        $this->assertEquals(0.0, $conversion->calculateRoi(0)); // Division by zero protection
        $this->assertEquals(-0.5, $conversion->calculateRoi(200)); // (100-200)/200 = -0.5
    }

    public function test_campaign_conversion_calculate_roas(): void
    {
        $conversion = CampaignConversion::factory()->create(['conversion_value' => 100]);

        $this->assertEquals(2.0, $conversion->calculateRoas(50)); // 100/50 = 2.0
        $this->assertEquals(0.0, $conversion->calculateRoas(0)); // Division by zero protection
        $this->assertEquals(0.5, $conversion->calculateRoas(200)); // 100/200 = 0.5
    }

    public function test_campaign_conversion_is_high_value(): void
    {
        $highValueConversion = CampaignConversion::factory()->create(['conversion_value' => 200]);
        $lowValueConversion = CampaignConversion::factory()->create(['conversion_value' => 50]);

        $this->assertTrue($highValueConversion->isHighValue(100));
        $this->assertFalse($lowValueConversion->isHighValue(100));
        $this->assertTrue($highValueConversion->isHighValue(150));
        $this->assertFalse($lowValueConversion->isHighValue(150));
    }

    public function test_campaign_conversion_is_recent(): void
    {
        $recentConversion = CampaignConversion::factory()->create(['converted_at' => now()->subDays(3)]);
        $oldConversion = CampaignConversion::factory()->create(['converted_at' => now()->subDays(10)]);

        $this->assertTrue($recentConversion->isRecent(7));
        $this->assertFalse($oldConversion->isRecent(7));
        $this->assertTrue($recentConversion->isRecent(5));
        $this->assertFalse($oldConversion->isRecent(5));
    }

    public function test_campaign_conversion_get_attribution_value(): void
    {
        $conversion = CampaignConversion::factory()->create([
            'conversion_value' => 100,
            'last_click_attribution' => 80,
            'first_click_attribution' => 60,
            'linear_attribution' => 70,
            'time_decay_attribution' => 75,
            'position_based_attribution' => 85,
            'data_driven_attribution' => 90,
        ]);

        $this->assertEquals(80, $conversion->getAttributionValue('last_click'));
        $this->assertEquals(60, $conversion->getAttributionValue('first_click'));
        $this->assertEquals(70, $conversion->getAttributionValue('linear'));
        $this->assertEquals(75, $conversion->getAttributionValue('time_decay'));
        $this->assertEquals(85, $conversion->getAttributionValue('position_based'));
        $this->assertEquals(90, $conversion->getAttributionValue('data_driven'));
        $this->assertEquals(80, $conversion->getAttributionValue('unknown')); // Default to last_click
    }

    public function test_campaign_conversion_casts_work(): void
    {
        $conversion = CampaignConversion::factory()->create([
            'conversion_value' => '150.75',
            'is_mobile' => '1',
            'is_tablet' => '0',
            'is_desktop' => '1',
            'conversion_duration' => '300',
            'page_views' => '5',
            'time_on_site' => '1200',
            'bounce_rate' => '0.25',
            'tags' => ['tag1', 'tag2'],
            'custom_attributes' => ['key' => 'value'],
        ]);

        $this->assertIsString($conversion->conversion_value);
        $this->assertEquals('150.75', $conversion->conversion_value);
        $this->assertIsBool($conversion->is_mobile);
        $this->assertTrue($conversion->is_mobile);
        $this->assertFalse($conversion->is_tablet);
        $this->assertTrue($conversion->is_desktop);
        $this->assertIsInt($conversion->conversion_duration);
        $this->assertEquals(300, $conversion->conversion_duration);
        $this->assertIsInt($conversion->page_views);
        $this->assertEquals(5, $conversion->page_views);
        $this->assertIsInt($conversion->time_on_site);
        $this->assertEquals(1200, $conversion->time_on_site);
        $this->assertIsString($conversion->bounce_rate);
        $this->assertEquals('0.25', $conversion->bounce_rate);
        $this->assertIsArray($conversion->tags);
        $this->assertEquals(['tag1', 'tag2'], $conversion->tags);
        $this->assertIsArray($conversion->custom_attributes);
        $this->assertEquals(['key' => 'value'], $conversion->custom_attributes);
    }

    public function test_campaign_conversion_factory_states(): void
    {
        $purchaseConversion = CampaignConversion::factory()->purchase()->create();
        $this->assertEquals('purchase', $purchaseConversion->conversion_type);
        $this->assertEquals('completed', $purchaseConversion->status);
        $this->assertGreaterThanOrEqual(10, $purchaseConversion->conversion_value);

        $signupConversion = CampaignConversion::factory()->signup()->create();
        $this->assertEquals('signup', $signupConversion->conversion_type);
        $this->assertEquals('completed', $signupConversion->status);
        $this->assertLessThanOrEqual(50, $signupConversion->conversion_value);

        $highValueConversion = CampaignConversion::factory()->highValue()->create();
        $this->assertGreaterThanOrEqual(500, $highValueConversion->conversion_value);

        $recentConversion = CampaignConversion::factory()->recent()->create();
        $this->assertTrue($recentConversion->converted_at->isAfter(now()->subDays(8)));

        $mobileConversion = CampaignConversion::factory()->mobile()->create();
        $this->assertEquals('mobile', $mobileConversion->device_type);
        $this->assertTrue($mobileConversion->is_mobile);
        $this->assertFalse($mobileConversion->is_tablet);
        $this->assertFalse($mobileConversion->is_desktop);

        $desktopConversion = CampaignConversion::factory()->desktop()->create();
        $this->assertEquals('desktop', $desktopConversion->device_type);
        $this->assertFalse($desktopConversion->is_mobile);
        $this->assertFalse($desktopConversion->is_tablet);
        $this->assertTrue($desktopConversion->is_desktop);

        $pendingConversion = CampaignConversion::factory()->pending()->create();
        $this->assertEquals('pending', $pendingConversion->status);

        $completedConversion = CampaignConversion::factory()->completed()->create();
        $this->assertEquals('completed', $completedConversion->status);
    }
}

