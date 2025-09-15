<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Campaign;
use App\Models\CampaignClick;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CampaignClickTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_click_can_be_created(): void
    {
        $campaign = Campaign::factory()->create();
        $click = CampaignClick::factory()->create([
            'campaign_id' => $campaign->id,
            'session_id' => 'test_session_123',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'click_type' => 'banner',
            'clicked_url' => 'https://example.com/product/123',
            'clicked_at' => now(),
            'device_type' => 'desktop',
            'browser' => 'Chrome',
            'os' => 'Windows',
            'country' => 'US',
            'city' => 'New York',
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'summer_sale',
            'conversion_value' => 25.50,
            'is_converted' => true,
            'conversion_data' => ['product_id' => 123, 'amount' => 25.50],
        ]);

        $this->assertInstanceOf(CampaignClick::class, $click);
        $this->assertEquals($campaign->id, $click->campaign_id);
        $this->assertEquals('test_session_123', $click->session_id);
        $this->assertEquals('192.168.1.1', $click->ip_address);
        $this->assertEquals('banner', $click->click_type);
        $this->assertEquals('https://example.com/product/123', $click->clicked_url);
        $this->assertEquals('desktop', $click->device_type);
        $this->assertEquals('Chrome', $click->browser);
        $this->assertEquals('Windows', $click->os);
        $this->assertEquals('US', $click->country);
        $this->assertEquals('New York', $click->city);
        $this->assertEquals('google', $click->utm_source);
        $this->assertEquals('cpc', $click->utm_medium);
        $this->assertEquals('summer_sale', $click->utm_campaign);
        $this->assertEquals(25.50, $click->conversion_value);
        $this->assertTrue($click->is_converted);
        $this->assertIsArray($click->conversion_data);
    }

    public function test_campaign_click_fillable_attributes(): void
    {
        $click = new CampaignClick();
        $fillable = $click->getFillable();

        $expectedFillable = [
            'campaign_id',
            'session_id',
            'ip_address',
            'user_agent',
            'click_type',
            'clicked_url',
            'customer_id',
            'clicked_at',
            'referer',
            'device_type',
            'browser',
            'os',
            'country',
            'city',
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_term',
            'utm_content',
            'conversion_value',
            'is_converted',
            'conversion_data',
        ];

        foreach ($expectedFillable as $field) {
            $this->assertContains($field, $fillable);
        }
    }

    public function test_campaign_click_casts(): void
    {
        $click = CampaignClick::factory()->create([
            'clicked_at' => '2024-01-01 12:00:00',
            'conversion_value' => '15.75',
            'is_converted' => true,
            'conversion_data' => ['test' => 'data'],
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $click->clicked_at);
        $this->assertIsString($click->conversion_value);
        $this->assertTrue($click->is_converted);
        $this->assertIsArray($click->conversion_data);
    }

    public function test_campaign_click_belongs_to_campaign(): void
    {
        $campaign = Campaign::factory()->create();
        $click = CampaignClick::factory()->create(['campaign_id' => $campaign->id]);

        $this->assertInstanceOf(Campaign::class, $click->campaign);
        $this->assertEquals($campaign->id, $click->campaign->id);
    }

    public function test_campaign_click_belongs_to_customer(): void
    {
        $customer = User::factory()->create();
        $click = CampaignClick::factory()->create(['customer_id' => $customer->id]);

        $this->assertInstanceOf(User::class, $click->customer);
        $this->assertEquals($customer->id, $click->customer->id);
    }

    public function test_campaign_click_has_translations(): void
    {
        $click = new CampaignClick();
        
        $this->assertTrue(method_exists($click, 'translations'));
        $this->assertTrue(method_exists($click, 'trans'));
    }

    public function test_campaign_click_scopes(): void
    {
        $campaign = Campaign::factory()->create();
        
        $convertedClick = CampaignClick::factory()->create([
            'campaign_id' => $campaign->id,
            'is_converted' => true,
        ]);
        
        $nonConvertedClick = CampaignClick::factory()->create([
            'campaign_id' => $campaign->id,
            'is_converted' => false,
        ]);

        // Test converted scope
        $convertedClicks = CampaignClick::converted()->get();
        $this->assertTrue($convertedClicks->contains($convertedClick));
        $this->assertFalse($convertedClicks->contains($nonConvertedClick));
    }

    public function test_campaign_click_scope_by_campaign(): void
    {
        $campaign1 = Campaign::factory()->create();
        $campaign2 = Campaign::factory()->create();
        
        $click1 = CampaignClick::factory()->create(['campaign_id' => $campaign1->id]);
        $click2 = CampaignClick::factory()->create(['campaign_id' => $campaign2->id]);

        $campaign1Clicks = CampaignClick::byCampaign($campaign1->id)->get();
        $this->assertTrue($campaign1Clicks->contains($click1));
        $this->assertFalse($campaign1Clicks->contains($click2));
    }

    public function test_campaign_click_scope_by_device_type(): void
    {
        $desktopClick = CampaignClick::factory()->create(['device_type' => 'desktop']);
        $mobileClick = CampaignClick::factory()->create(['device_type' => 'mobile']);

        $desktopClicks = CampaignClick::byDeviceType('desktop')->get();
        $this->assertTrue($desktopClicks->contains($desktopClick));
        $this->assertFalse($desktopClicks->contains($mobileClick));
    }

    public function test_campaign_click_scope_by_customer(): void
    {
        $customer1 = User::factory()->create();
        $customer2 = User::factory()->create();
        
        $click1 = CampaignClick::factory()->create(['customer_id' => $customer1->id]);
        $click2 = CampaignClick::factory()->create(['customer_id' => $customer2->id]);

        $customer1Clicks = CampaignClick::byCustomer($customer1->id)->get();
        $this->assertTrue($customer1Clicks->contains($click1));
        $this->assertFalse($customer1Clicks->contains($click2));
    }

    public function test_campaign_click_scope_by_country(): void
    {
        $usClick = CampaignClick::factory()->create(['country' => 'US']);
        $ukClick = CampaignClick::factory()->create(['country' => 'UK']);

        $usClicks = CampaignClick::byCountry('US')->get();
        $this->assertTrue($usClicks->contains($usClick));
        $this->assertFalse($usClicks->contains($ukClick));
    }

    public function test_campaign_click_scope_by_utm_source(): void
    {
        $googleClick = CampaignClick::factory()->create(['utm_source' => 'google']);
        $facebookClick = CampaignClick::factory()->create(['utm_source' => 'facebook']);

        $googleClicks = CampaignClick::byUtmSource('google')->get();
        $this->assertTrue($googleClicks->contains($googleClick));
        $this->assertFalse($googleClicks->contains($facebookClick));
    }

    public function test_campaign_click_scope_by_click_type(): void
    {
        $bannerClick = CampaignClick::factory()->create(['click_type' => 'banner']);
        $emailClick = CampaignClick::factory()->create(['click_type' => 'email']);

        $bannerClicks = CampaignClick::byClickType('banner')->get();
        $this->assertTrue($bannerClicks->contains($bannerClick));
        $this->assertFalse($bannerClicks->contains($emailClick));
    }

    public function test_campaign_click_scope_recent(): void
    {
        $recentClick = CampaignClick::factory()->create(['clicked_at' => now()]);
        $oldClick = CampaignClick::factory()->create(['clicked_at' => now()->subDays(35)]);

        $recentClicks = CampaignClick::recent()->get();
        $this->assertTrue($recentClicks->contains($recentClick));
        $this->assertFalse($recentClicks->contains($oldClick));
    }

    public function test_campaign_click_scope_by_date_range(): void
    {
        $startDate = now()->subDays(5);
        $endDate = now()->subDays(1);
        
        $inRangeClick = CampaignClick::factory()->create(['clicked_at' => now()->subDays(3)]);
        $outOfRangeClick = CampaignClick::factory()->create(['clicked_at' => now()->subDays(10)]);

        $rangeClicks = CampaignClick::byDateRange($startDate->format('Y-m-d'), $endDate->format('Y-m-d'))->get();
        $this->assertTrue($rangeClicks->contains($inRangeClick));
        $this->assertFalse($rangeClicks->contains($outOfRangeClick));
    }

    public function test_campaign_click_table_name(): void
    {
        $click = new CampaignClick();
        $this->assertEquals('campaign_clicks', $click->getTable());
    }

    public function test_campaign_click_no_timestamps(): void
    {
        $click = new CampaignClick();
        $this->assertFalse($click->timestamps);
    }

    public function test_campaign_click_factory(): void
    {
        $click = CampaignClick::factory()->create();

        $this->assertInstanceOf(CampaignClick::class, $click);
        $this->assertNotEmpty($click->campaign_id);
        $this->assertNotEmpty($click->session_id);
        $this->assertNotEmpty($click->ip_address);
        $this->assertNotEmpty($click->user_agent);
        $this->assertNotEmpty($click->click_type);
        $this->assertNotEmpty($click->clicked_url);
    }
}
