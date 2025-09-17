<?php declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\CampaignClick;
use App\Models\Campaign;
use App\Models\User;
use App\Models\CampaignConversion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignClickTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_click_can_be_created(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();

        $click = CampaignClick::factory()->create([
            'campaign_id' => $campaign->id,
            'customer_id' => $user->id,
        ]);

        $this->assertInstanceOf(CampaignClick::class, $click);
        $this->assertEquals($campaign->id, $click->campaign_id);
        $this->assertEquals($user->id, $click->customer_id);
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
        $user = User::factory()->create();
        $click = CampaignClick::factory()->create(['customer_id' => $user->id]);

        $this->assertInstanceOf(User::class, $click->customer);
        $this->assertEquals($user->id, $click->customer->id);
    }

    public function test_campaign_click_has_many_conversions(): void
    {
        $click = CampaignClick::factory()->create();
        $conversion = CampaignConversion::factory()->create(['click_id' => $click->id]);

        $this->assertTrue($click->conversions->contains($conversion));
    }

    public function test_campaign_click_can_be_converted(): void
    {
        $click = CampaignClick::factory()->converted()->create();

        $this->assertTrue($click->is_converted);
        $this->assertTrue($click->isConverted());
        $this->assertGreaterThan(0, $click->conversion_value);
    }

    public function test_campaign_click_scope_by_campaign(): void
    {
        $campaign1 = Campaign::factory()->create();
        $campaign2 = Campaign::factory()->create();
        
        $click1 = CampaignClick::factory()->create(['campaign_id' => $campaign1->id]);
        $click2 = CampaignClick::factory()->create(['campaign_id' => $campaign2->id]);

        $results = CampaignClick::byCampaign($campaign1->id)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($click1));
        $this->assertFalse($results->contains($click2));
    }

    public function test_campaign_click_scope_by_customer(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $click1 = CampaignClick::factory()->create(['customer_id' => $user1->id]);
        $click2 = CampaignClick::factory()->create(['customer_id' => $user2->id]);

        $results = CampaignClick::byCustomer($user1->id)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($click1));
        $this->assertFalse($results->contains($click2));
    }

    public function test_campaign_click_scope_by_click_type(): void
    {
        $ctaClick = CampaignClick::factory()->cta()->create();
        $bannerClick = CampaignClick::factory()->banner()->create();

        $results = CampaignClick::byClickType('cta')->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($ctaClick));
        $this->assertFalse($results->contains($bannerClick));
    }

    public function test_campaign_click_scope_converted(): void
    {
        $convertedClick = CampaignClick::factory()->converted()->create();
        $regularClick = CampaignClick::factory()->create();

        $results = CampaignClick::converted()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($convertedClick));
        $this->assertFalse($results->contains($regularClick));
    }

    public function test_campaign_click_scope_by_device_type(): void
    {
        $mobileClick = CampaignClick::factory()->mobile()->create();
        $desktopClick = CampaignClick::factory()->desktop()->create();

        $results = CampaignClick::byDeviceType('mobile')->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($mobileClick));
        $this->assertFalse($results->contains($desktopClick));
    }

    public function test_campaign_click_scope_recent(): void
    {
        $recentClick = CampaignClick::factory()->recent()->create();
        $oldClick = CampaignClick::factory()->create(['clicked_at' => now()->subDays(40)]);

        $results = CampaignClick::recent(30)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($recentClick));
        $this->assertFalse($results->contains($oldClick));
    }

    public function test_campaign_click_click_type_label(): void
    {
        $click = CampaignClick::factory()->cta()->create();

        $this->assertEquals(__('campaign_clicks.click_type.cta'), $click->click_type_label);
    }

    public function test_campaign_click_device_type_label(): void
    {
        $click = CampaignClick::factory()->mobile()->create();

        $this->assertEquals(__('campaign_clicks.device_type.mobile'), $click->device_type_label);
    }

    public function test_campaign_click_browser_label(): void
    {
        $click = CampaignClick::factory()->create(['browser' => 'chrome']);

        $this->assertEquals(__('campaign_clicks.browser.chrome'), $click->browser_label);
    }

    public function test_campaign_click_os_label(): void
    {
        $click = CampaignClick::factory()->create(['os' => 'windows']);

        $this->assertEquals(__('campaign_clicks.os.windows'), $click->os_label);
    }

    public function test_campaign_click_get_conversion_rate(): void
    {
        $click = CampaignClick::factory()->create();
        $conversion = CampaignConversion::factory()->create(['click_id' => $click->id]);

        $this->assertEquals(100.0, $click->getConversionRate());
    }

    public function test_campaign_click_get_total_conversion_value(): void
    {
        $click = CampaignClick::factory()->create();
        CampaignConversion::factory()->create([
            'click_id' => $click->id,
            'conversion_value' => 100.50
        ]);
        CampaignConversion::factory()->create([
            'click_id' => $click->id,
            'conversion_value' => 50.25
        ]);

        $this->assertEquals(150.75, $click->getTotalConversionValue());
    }

    public function test_campaign_click_get_utm_params(): void
    {
        $click = CampaignClick::factory()->withUtm()->create();

        $utmParams = $click->getUtmParams();

        $this->assertArrayHasKey('utm_source', $utmParams);
        $this->assertArrayHasKey('utm_medium', $utmParams);
        $this->assertArrayHasKey('utm_campaign', $utmParams);
        $this->assertArrayHasKey('utm_term', $utmParams);
        $this->assertArrayHasKey('utm_content', $utmParams);
    }

    public function test_campaign_click_get_location_info(): void
    {
        $click = CampaignClick::factory()->create([
            'country' => 'Lithuania',
            'city' => 'Vilnius',
            'ip_address' => '192.168.1.1'
        ]);

        $locationInfo = $click->getLocationInfo();

        $this->assertEquals('Lithuania', $locationInfo['country']);
        $this->assertEquals('Vilnius', $locationInfo['city']);
        $this->assertEquals('192.168.1.1', $locationInfo['ip_address']);
    }

    public function test_campaign_click_get_device_info(): void
    {
        $click = CampaignClick::factory()->create([
            'device_type' => 'mobile',
            'browser' => 'chrome',
            'os' => 'android',
            'user_agent' => 'Mozilla/5.0...'
        ]);

        $deviceInfo = $click->getDeviceInfo();

        $this->assertEquals('mobile', $deviceInfo['device_type']);
        $this->assertEquals('chrome', $deviceInfo['browser']);
        $this->assertEquals('android', $deviceInfo['os']);
        $this->assertEquals('Mozilla/5.0...', $deviceInfo['user_agent']);
    }

    public function test_campaign_click_casts(): void
    {
        $click = CampaignClick::factory()->create([
            'clicked_at' => '2024-01-01 12:00:00',
            'conversion_value' => '100.50',
            'is_converted' => '1',
            'conversion_data' => '{"key": "value"}'
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $click->clicked_at);
        $this->assertIsFloat($click->conversion_value);
        $this->assertIsBool($click->is_converted);
        $this->assertIsArray($click->conversion_data);
    }

    public function test_campaign_click_fillable_attributes(): void
    {
        $fillable = [
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

        $click = new CampaignClick();
        $this->assertEquals($fillable, $click->getFillable());
    }
}
