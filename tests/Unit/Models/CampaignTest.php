<?php declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Campaign;
use App\Models\CampaignClick;
use App\Models\CampaignConversion;
use App\Models\CampaignCustomerSegment;
use App\Models\CampaignProductTarget;
use App\Models\CampaignSchedule;
use App\Models\CampaignTranslation;
use App\Models\CampaignView;
use App\Models\Category;
use App\Models\Channel;
use App\Models\CustomerGroup;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CampaignTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_can_be_created(): void
    {
        $campaign = Campaign::factory()->create([
            'name' => 'Test Campaign',
            'type' => 'email',
            'status' => 'active',
            'budget' => 1000.0,
        ]);

        $this->assertDatabaseHas('discount_campaigns', [
            'name' => 'Test Campaign',
            'type' => 'email',
            'status' => 'active',
            'budget' => 1000.0,
        ]);

        $this->assertEquals('Test Campaign', $campaign->name);
        $this->assertEquals('email', $campaign->type);
        $this->assertEquals('active', $campaign->status);
        $this->assertEquals(1000.0, $campaign->budget);
    }

    public function test_campaign_has_translations(): void
    {
        $campaign = Campaign::factory()->create();

        $translation = CampaignTranslation::factory()->create([
            'campaign_id' => $campaign->id,
            'locale' => 'en',
            'name' => 'English Name',
            'description' => 'English Description',
        ]);

        $this->assertTrue($campaign->translations->contains($translation));
        $this->assertEquals('English Name', $campaign->trans('name', 'en'));
        $this->assertEquals('English Description', $campaign->trans('description', 'en'));
    }

    public function test_campaign_belongs_to_channel(): void
    {
        $channel = Channel::factory()->create();
        $campaign = Campaign::factory()->create(['channel_id' => $channel->id]);

        $this->assertInstanceOf(Channel::class, $campaign->channel);
        $this->assertEquals($channel->id, $campaign->channel->id);
    }

    public function test_campaign_belongs_to_zone(): void
    {
        $zone = Zone::factory()->create();
        $campaign = Campaign::factory()->create(['zone_id' => $zone->id]);

        $this->assertInstanceOf(Zone::class, $campaign->zone);
        $this->assertEquals($zone->id, $campaign->zone->id);
    }

    public function test_campaign_has_many_discounts(): void
    {
        $campaign = Campaign::factory()->create();
        $discounts = Discount::factory()->count(3)->create();

        $campaign->discounts()->attach($discounts->pluck('id'));

        $this->assertCount(3, $campaign->discounts);
        $this->assertTrue($campaign->discounts->contains($discounts->first()));
    }

    public function test_campaign_has_many_target_categories(): void
    {
        $campaign = Campaign::factory()->create();
        $categories = Category::factory()->count(3)->create();

        $campaign->targetCategories()->attach($categories->pluck('id'));

        $this->assertCount(3, $campaign->targetCategories);
        $this->assertTrue($campaign->targetCategories->contains($categories->first()));
    }

    public function test_campaign_has_many_target_products(): void
    {
        $campaign = Campaign::factory()->create();
        $products = Product::factory()->count(3)->create();

        $campaign->targetProducts()->attach($products->pluck('id'));

        $this->assertCount(3, $campaign->targetProducts);
        $this->assertTrue($campaign->targetProducts->contains($products->first()));
    }

    public function test_campaign_has_many_target_customer_groups(): void
    {
        $campaign = Campaign::factory()->create();
        $customerGroups = CustomerGroup::factory()->count(3)->create();

        $campaign->targetCustomerGroups()->attach($customerGroups->pluck('id'));

        $this->assertCount(3, $campaign->targetCustomerGroups);
        $this->assertTrue($campaign->targetCustomerGroups->contains($customerGroups->first()));
    }

    public function test_campaign_has_many_views(): void
    {
        $campaign = Campaign::factory()->create();
        $views = CampaignView::factory()->count(3)->create(['campaign_id' => $campaign->id]);

        $this->assertCount(3, $campaign->views);
        $this->assertTrue($campaign->views->contains($views->first()));
    }

    public function test_campaign_has_many_clicks(): void
    {
        $campaign = Campaign::factory()->create();
        $clicks = CampaignClick::factory()->count(3)->create(['campaign_id' => $campaign->id]);

        $this->assertCount(3, $campaign->clicks);
        $this->assertTrue($campaign->clicks->contains($clicks->first()));
    }

    public function test_campaign_has_many_conversions(): void
    {
        $campaign = Campaign::factory()->create();
        $conversions = CampaignConversion::factory()->count(3)->create(['campaign_id' => $campaign->id]);

        $this->assertCount(3, $campaign->conversions);
        $this->assertTrue($campaign->conversions->contains($conversions->first()));
    }

    public function test_campaign_has_many_customer_segments(): void
    {
        $campaign = Campaign::factory()->create();
        $segments = CampaignCustomerSegment::factory()->count(3)->create(['campaign_id' => $campaign->id]);

        $this->assertCount(3, $campaign->customerSegments);
        $this->assertTrue($campaign->customerSegments->contains($segments->first()));
    }

    public function test_campaign_has_many_product_targets(): void
    {
        $campaign = Campaign::factory()->create();
        $targets = CampaignProductTarget::factory()->count(3)->create(['campaign_id' => $campaign->id]);

        $this->assertCount(3, $campaign->productTargets);
        $this->assertTrue($campaign->productTargets->contains($targets->first()));
    }

    public function test_campaign_has_many_schedules(): void
    {
        $campaign = Campaign::factory()->create();
        $schedules = CampaignSchedule::factory()->count(3)->create(['campaign_id' => $campaign->id]);

        $this->assertCount(3, $campaign->schedules);
        $this->assertTrue($campaign->schedules->contains($schedules->first()));
    }

    public function test_active_scope(): void
    {
        $activeCampaign = Campaign::factory()->create([
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $inactiveCampaign = Campaign::factory()->create([
            'status' => 'paused',
        ]);

        $expiredCampaign = Campaign::factory()->create([
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->subHour(),
        ]);

        $activeCampaigns = Campaign::active()->get();

        $this->assertTrue($activeCampaigns->contains($activeCampaign));
        $this->assertFalse($activeCampaigns->contains($inactiveCampaign));
        $this->assertFalse($activeCampaigns->contains($expiredCampaign));
    }

    public function test_scheduled_scope(): void
    {
        $scheduledCampaign = Campaign::factory()->create(['status' => 'scheduled']);
        $activeCampaign = Campaign::factory()->create(['status' => 'active']);

        $scheduledCampaigns = Campaign::scheduled()->get();

        $this->assertTrue($scheduledCampaigns->contains($scheduledCampaign));
        $this->assertFalse($scheduledCampaigns->contains($activeCampaign));
    }

    public function test_expired_scope(): void
    {
        $expiredCampaign = Campaign::factory()->create([
            'status' => 'expired',
        ]);

        $expiredByDate = Campaign::factory()->create([
            'status' => 'active',
            'ends_at' => now()->subDay(),
        ]);

        $activeCampaign = Campaign::factory()->create([
            'status' => 'active',
            'ends_at' => now()->addDay(),
        ]);

        $expiredCampaigns = Campaign::expired()->get();

        $this->assertTrue($expiredCampaigns->contains($expiredCampaign));
        $this->assertTrue($expiredCampaigns->contains($expiredByDate));
        $this->assertFalse($expiredCampaigns->contains($activeCampaign));
    }

    public function test_featured_scope(): void
    {
        $featuredCampaign = Campaign::factory()->create(['is_featured' => true]);
        $regularCampaign = Campaign::factory()->create(['is_featured' => false]);

        $featuredCampaigns = Campaign::featured()->get();

        $this->assertTrue($featuredCampaigns->contains($featuredCampaign));
        $this->assertFalse($featuredCampaigns->contains($regularCampaign));
    }

    public function test_by_priority_scope(): void
    {
        $campaign1 = Campaign::factory()->create(['display_priority' => 10]);
        $campaign2 = Campaign::factory()->create(['display_priority' => 5]);
        $campaign3 = Campaign::factory()->create(['display_priority' => 15]);

        $campaigns = Campaign::byPriority()->get();

        $this->assertEquals($campaign3->id, $campaigns->first()->id);
        $this->assertEquals($campaign1->id, $campaigns->skip(1)->first()->id);
        $this->assertEquals($campaign2->id, $campaigns->last()->id);
    }

    public function test_for_channel_scope(): void
    {
        $channel = Channel::factory()->create();
        $campaign1 = Campaign::factory()->create(['channel_id' => $channel->id]);
        $campaign2 = Campaign::factory()->create(['channel_id' => Channel::factory()->create()->id]);

        $campaigns = Campaign::forChannel($channel->id)->get();

        $this->assertTrue($campaigns->contains($campaign1));
        $this->assertFalse($campaigns->contains($campaign2));
    }

    public function test_for_zone_scope(): void
    {
        $zone = Zone::factory()->create();
        $campaign1 = Campaign::factory()->create(['zone_id' => $zone->id]);
        $campaign2 = Campaign::factory()->create(['zone_id' => Zone::factory()->create()->id]);

        $campaigns = Campaign::forZone($zone->id)->get();

        $this->assertTrue($campaigns->contains($campaign1));
        $this->assertFalse($campaigns->contains($campaign2));
    }

    public function test_with_analytics_scope(): void
    {
        $trackingCampaign = Campaign::factory()->create(['track_conversions' => true]);
        $noTrackingCampaign = Campaign::factory()->create(['track_conversions' => false]);

        $campaigns = Campaign::withAnalytics()->get();

        $this->assertTrue($campaigns->contains($trackingCampaign));
        $this->assertFalse($campaigns->contains($noTrackingCampaign));
    }

    public function test_social_media_ready_scope(): void
    {
        $socialCampaign = Campaign::factory()->create(['social_media_ready' => true]);
        $regularCampaign = Campaign::factory()->create(['social_media_ready' => false]);

        $campaigns = Campaign::socialMediaReady()->get();

        $this->assertTrue($campaigns->contains($socialCampaign));
        $this->assertFalse($campaigns->contains($regularCampaign));
    }

    public function test_is_active_method(): void
    {
        $activeCampaign = Campaign::factory()->create([
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $inactiveCampaign = Campaign::factory()->create([
            'status' => 'paused',
        ]);

        $this->assertTrue($activeCampaign->isActive());
        $this->assertFalse($inactiveCampaign->isActive());
    }

    public function test_is_expired_method(): void
    {
        $expiredCampaign = Campaign::factory()->create([
            'ends_at' => now()->subDay(),
        ]);

        $activeCampaign = Campaign::factory()->create([
            'ends_at' => now()->addDay(),
        ]);

        $this->assertTrue($expiredCampaign->isExpired());
        $this->assertFalse($activeCampaign->isExpired());
    }

    public function test_is_scheduled_method(): void
    {
        $scheduledCampaign = Campaign::factory()->create([
            'status' => 'scheduled',
            'starts_at' => now()->addDay(),
        ]);

        $activeCampaign = Campaign::factory()->create([
            'status' => 'active',
            'starts_at' => now()->subDay(),
        ]);

        $this->assertTrue($scheduledCampaign->isScheduled());
        $this->assertFalse($activeCampaign->isScheduled());
    }

    public function test_is_within_budget_method(): void
    {
        $campaignWithinBudget = Campaign::factory()->create([
            'budget_limit' => 1000,
            'total_revenue' => 500,
        ]);

        $campaignOverBudget = Campaign::factory()->create([
            'budget_limit' => 1000,
            'total_revenue' => 1200,
        ]);

        $campaignNoLimit = Campaign::factory()->create([
            'budget_limit' => null,
            'total_revenue' => 500,
        ]);

        $this->assertTrue($campaignWithinBudget->isWithinBudget());
        $this->assertFalse($campaignOverBudget->isWithinBudget());
        $this->assertTrue($campaignNoLimit->isWithinBudget());
    }

    public function test_get_click_through_rate_method(): void
    {
        $campaign = Campaign::factory()->create([
            'total_views' => 1000,
            'total_clicks' => 50,
        ]);

        $this->assertEquals(5.0, $campaign->getClickThroughRate());
    }

    public function test_get_click_through_rate_with_zero_views(): void
    {
        $campaign = Campaign::factory()->create([
            'total_views' => 0,
            'total_clicks' => 50,
        ]);

        $this->assertEquals(0.0, $campaign->getClickThroughRate());
    }

    public function test_get_conversion_rate_method(): void
    {
        $campaign = Campaign::factory()->create([
            'total_clicks' => 100,
            'total_conversions' => 5,
        ]);

        $this->assertEquals(5.0, $campaign->getConversionRate());
    }

    public function test_get_conversion_rate_with_zero_clicks(): void
    {
        $campaign = Campaign::factory()->create([
            'total_clicks' => 0,
            'total_conversions' => 5,
        ]);

        $this->assertEquals(0.0, $campaign->getConversionRate());
    }

    public function test_get_roi_method(): void
    {
        $campaign = Campaign::factory()->create([
            'budget_limit' => 1000,
            'total_revenue' => 1200,
        ]);

        $this->assertEquals(20.0, $campaign->getROI());
    }

    public function test_get_roi_with_zero_budget(): void
    {
        $campaign = Campaign::factory()->create([
            'budget_limit' => 0,
            'total_revenue' => 1200,
        ]);

        $this->assertEquals(0.0, $campaign->getROI());
    }

    public function test_record_view_method(): void
    {
        $campaign = Campaign::factory()->create(['total_views' => 0]);

        $campaign->recordView('session123', '192.168.1.1', 'Mozilla/5.0', 'https://example.com', 1);

        $this->assertDatabaseHas('campaign_views', [
            'campaign_id' => $campaign->id,
            'session_id' => 'session123',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'referer' => 'https://example.com',
            'customer_id' => 1,
        ]);

        $this->assertEquals(1, $campaign->fresh()->total_views);
    }

    public function test_record_click_method(): void
    {
        $campaign = Campaign::factory()->create(['total_clicks' => 0]);

        $campaign->recordClick('cta', 'https://example.com', 'session123', '192.168.1.1', 'Mozilla/5.0', 1);

        $this->assertDatabaseHas('campaign_clicks', [
            'campaign_id' => $campaign->id,
            'session_id' => 'session123',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'click_type' => 'cta',
            'clicked_url' => 'https://example.com',
            'customer_id' => 1,
        ]);

        $this->assertEquals(1, $campaign->fresh()->total_clicks);
    }

    public function test_record_conversion_method(): void
    {
        $campaign = Campaign::factory()->create([
            'total_conversions' => 0,
            'total_revenue' => 0,
        ]);

        $campaign->recordConversion('purchase', 100.5, 1, 1, 'session123', ['order_total' => 100.5]);

        $this->assertDatabaseHas('campaign_conversions', [
            'campaign_id' => $campaign->id,
            'order_id' => 1,
            'customer_id' => 1,
            'conversion_type' => 'purchase',
            'conversion_value' => 100.5,
            'session_id' => 'session123',
            'conversion_data' => json_encode(['order_total' => 100.5]),
        ]);

        $freshCampaign = $campaign->fresh();
        $this->assertEquals(1, $freshCampaign->total_conversions);
        $this->assertEquals(100.5, $freshCampaign->total_revenue);
    }

    public function test_get_banner_url_method(): void
    {
        $campaignWithBanner = Campaign::factory()->create(['banner_image' => 'banner.jpg']);
        $campaignWithoutBanner = Campaign::factory()->create(['banner_image' => null]);

        $this->assertStringContains('storage/campaigns/banner.jpg', $campaignWithBanner->getBannerUrl());
        $this->assertNull($campaignWithoutBanner->getBannerUrl());
    }

    public function test_get_status_badge_color_method(): void
    {
        $this->assertEquals('success', Campaign::factory()->create(['status' => 'active'])->getStatusBadgeColor());
        $this->assertEquals('warning', Campaign::factory()->create(['status' => 'scheduled'])->getStatusBadgeColor());
        $this->assertEquals('secondary', Campaign::factory()->create(['status' => 'paused'])->getStatusBadgeColor());
        $this->assertEquals('danger', Campaign::factory()->create(['status' => 'expired'])->getStatusBadgeColor());
        $this->assertEquals('info', Campaign::factory()->create(['status' => 'draft'])->getStatusBadgeColor());
        $this->assertEquals('secondary', Campaign::factory()->create(['status' => 'unknown'])->getStatusBadgeColor());
    }

    public function test_get_status_label_method(): void
    {
        $this->assertEquals(__('campaigns.status.active'), Campaign::factory()->create(['status' => 'active'])->getStatusLabel());
        $this->assertEquals(__('campaigns.status.scheduled'), Campaign::factory()->create(['status' => 'scheduled'])->getStatusLabel());
        $this->assertEquals(__('campaigns.status.paused'), Campaign::factory()->create(['status' => 'paused'])->getStatusLabel());
        $this->assertEquals(__('campaigns.status.expired'), Campaign::factory()->create(['status' => 'expired'])->getStatusLabel());
        $this->assertEquals(__('campaigns.status.draft'), Campaign::factory()->create(['status' => 'draft'])->getStatusLabel());
        $this->assertEquals(__('campaigns.status.unknown'), Campaign::factory()->create(['status' => 'unknown'])->getStatusLabel());
    }

    public function test_campaign_uses_route_key_name(): void
    {
        $campaign = Campaign::factory()->create(['slug' => 'test-campaign']);

        $this->assertEquals('slug', $campaign->getRouteKeyName());
        $this->assertEquals('test-campaign', $campaign->getRouteKey());
    }
}
