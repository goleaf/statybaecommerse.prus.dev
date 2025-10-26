<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Campaign;
use App\Models\CampaignClick;
use App\Models\CampaignConversion;
use App\Models\CampaignView;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class CampaignControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_campaigns_index(): void
    {
        $campaigns = Campaign::factory()->count(3)->create([
            'status'    => 'active',
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDay(),
        ]);

        $response = $this->get(route('frontend.campaigns.index'));

        $response->assertStatus(200);
        $response->assertViewIs('campaigns.index');
        $response->assertViewHas('campaigns');
        $response->assertSee($campaigns->first()->trans('name'));
    }

    public function test_can_filter_campaigns_by_type(): void
    {
        $emailCampaign = Campaign::factory()->create([
            'type'      => 'email',
            'status'    => 'active',
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDay(),
        ]);

        $smsCampaign = Campaign::factory()->create([
            'type'      => 'sms',
            'status'    => 'active',
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDay(),
        ]);

        $response = $this->get(route('frontend.campaigns.index', ['type' => 'email']));

        $response->assertStatus(200);
        $response->assertSee($emailCampaign->trans('name'));
        $response->assertDontSee($smsCampaign->trans('name'));
    }

    public function test_can_search_campaigns(): void
    {
        $searchableCampaign = Campaign::factory()->create([
            'name'      => 'Searchable Campaign',
            'status'    => 'active',
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDay(),
        ]);

        $otherCampaign = Campaign::factory()->create([
            'name'      => 'Other Campaign',
            'status'    => 'active',
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDay(),
        ]);

        $response = $this->get(route('frontend.campaigns.index', ['search' => 'Searchable']));

        $response->assertStatus(200);
        $response->assertSee($searchableCampaign->trans('name'));
        $response->assertDontSee($otherCampaign->trans('name'));
    }

    public function test_can_view_featured_campaigns_page(): void
    {
        $featuredCampaign = Campaign::factory()->create([
            'is_featured' => true,
            'status'      => 'active',
            'starts_at'   => now()->subDay(),
            'ends_at'     => now()->addDay(),
        ]);

        Campaign::factory()->create([
            'is_featured' => false,
            'status'      => 'active',
            'starts_at'   => now()->subDay(),
            'ends_at'     => now()->addDay(),
        ]);

        $response = $this->get(route('frontend.campaigns.featured'));

        $response->assertStatus(200);
        $response->assertViewIs('campaigns.featured');
        $response->assertViewHas('campaigns');
        $response->assertSee($featuredCampaign->trans('name'));
    }

    public function test_can_view_campaigns_by_type_page(): void
    {
        $emailCampaign = Campaign::factory()->create([
            'type'      => 'email',
            'status'    => 'active',
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDay(),
        ]);

        $smsCampaign = Campaign::factory()->create([
            'type'      => 'sms',
            'status'    => 'active',
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDay(),
        ]);

        $response = $this->get(route('frontend.campaigns.by-type', ['type' => 'email']));

        $response->assertStatus(200);
        $response->assertViewIs('campaigns.by-type');
        $response->assertViewHas('campaigns');
        $response->assertSee($emailCampaign->trans('name'));
        $response->assertDontSee($smsCampaign->trans('name'));
    }

    public function test_can_view_campaign_search_page(): void
    {
        $matchingCampaign = Campaign::factory()->create([
            'name'      => 'Holiday Specials',
            'status'    => 'active',
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDay(),
        ]);

        Campaign::factory()->create([
            'name'      => 'Everyday Deals',
            'status'    => 'active',
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDay(),
        ]);

        $response = $this->get(route('frontend.campaigns.search', ['q' => 'Holiday']));

        $response->assertStatus(200);
        $response->assertViewIs('campaigns.search');
        $response->assertViewHas('campaigns');
        $response->assertSee($matchingCampaign->trans('name'));
        $response->assertSee('Holiday');
    }

    public function test_can_view_single_campaign(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create();

        $campaign = Campaign::factory()->create([
            'status'    => 'active',
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDay(),
        ]);

        $campaign->targetCategories()->attach($category->id);
        $campaign->targetProducts()->attach($product->id);

        $response = $this->get(route('frontend.campaigns.show', $campaign));

        $response->assertStatus(200);
        $response->assertViewIs('campaigns.show');
        $response->assertViewHas('campaign');
        $response->assertViewHas('relatedCampaigns');
        $response->assertSee($campaign->trans('name'));
        $response->assertSee($category->trans('name'));
        $response->assertSee($product->trans('name'));
    }

    public function test_campaign_view_records_analytics(): void
    {
        $campaign = Campaign::factory()->create([
            'status'      => 'active',
            'starts_at'   => now()->subDay(),
            'ends_at'     => now()->addDay(),
            'total_views' => 0,
        ]);

        $this->get(route('frontend.campaigns.show', $campaign));

        $this->assertDatabaseHas('campaign_views', [
            'campaign_id' => $campaign->id,
            'ip_address'  => '127.0.0.1',
        ]);

        $this->assertEquals(1, $campaign->fresh()->total_views);
    }

    public function test_campaign_view_records_analytics_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create([
            'status'      => 'active',
            'starts_at'   => now()->subDay(),
            'ends_at'     => now()->addDay(),
            'total_views' => 0,
        ]);

        $this
            ->actingAs($user)
            ->get(route('frontend.campaigns.show', $campaign));

        $this->assertDatabaseHas('campaign_views', [
            'campaign_id' => $campaign->id,
            'customer_id' => $user->id,
            'ip_address'  => '127.0.0.1',
        ]);
    }

    public function test_can_record_campaign_click(): void
    {
        $campaign = Campaign::factory()->create([
            'status'       => 'active',
            'starts_at'    => now()->subDay(),
            'ends_at'      => now()->addDay(),
            'total_clicks' => 0,
        ]);

        $response = $this->postJson(route('frontend.campaigns.click', $campaign), [
            'type' => 'cta',
            'url'  => 'https://example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => __('campaigns.messages.click_recorded'),
        ]);

        $this->assertDatabaseHas('campaign_clicks', [
            'campaign_id' => $campaign->id,
            'click_type'  => 'cta',
            'clicked_url' => 'https://example.com',
            'ip_address'  => '127.0.0.1',
        ]);

        $this->assertEquals(1, $campaign->fresh()->total_clicks);
    }

    public function test_can_record_campaign_click_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create([
            'status'       => 'active',
            'starts_at'    => now()->subDay(),
            'ends_at'      => now()->addDay(),
            'total_clicks' => 0,
        ]);

        $this
            ->actingAs($user)
            ->postJson(route('frontend.campaigns.click', $campaign), [
                'type' => 'banner',
                'url'  => 'https://example.com',
            ]);

        $this->assertDatabaseHas('campaign_clicks', [
            'campaign_id' => $campaign->id,
            'click_type'  => 'banner',
            'clicked_url' => 'https://example.com',
            'customer_id' => $user->id,
        ]);
    }

    public function test_can_record_campaign_conversion(): void
    {
        $campaign = Campaign::factory()->create([
            'status'            => 'active',
            'starts_at'         => now()->subDay(),
            'ends_at'           => now()->addDay(),
            'total_conversions' => 0,
            'total_revenue'     => 0,
        ]);

        $response = $this->postJson(route('frontend.campaigns.conversion', $campaign), [
            'type'     => 'purchase',
            'value'    => 100.5,
            'order_id' => 1,
            'data'     => ['order_total' => 100.5],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => __('campaigns.messages.conversion_recorded'),
        ]);

        $this->assertDatabaseHas('campaign_conversions', [
            'campaign_id'      => $campaign->id,
            'conversion_type'  => 'purchase',
            'conversion_value' => 100.5,
            'order_id'         => 1,
            'conversion_data'  => json_encode(['order_total' => 100.5]),
        ]);

        $freshCampaign = $campaign->fresh();
        $this->assertEquals(1, $freshCampaign->total_conversions);
        $this->assertEquals(100.5, $freshCampaign->total_revenue);
    }

    public function test_can_record_campaign_conversion_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create([
            'status'            => 'active',
            'starts_at'         => now()->subDay(),
            'ends_at'           => now()->addDay(),
            'total_conversions' => 0,
            'total_revenue'     => 0,
        ]);

        $this
            ->actingAs($user)
            ->postJson(route('frontend.campaigns.conversion', $campaign), [
                'type'  => 'signup',
                'value' => 0,
            ]);

        $this->assertDatabaseHas('campaign_conversions', [
            'campaign_id'      => $campaign->id,
            'conversion_type'  => 'signup',
            'conversion_value' => 0,
            'customer_id'      => $user->id,
        ]);
    }

    public function test_can_view_featured_campaigns(): void
    {
        $featuredCampaigns = Campaign::factory()->count(3)->create([
            'is_featured' => true,
            'status'      => 'active',
            'starts_at'   => now()->subDay(),
            'ends_at'     => now()->addDay(),
        ]);

        $regularCampaign = Campaign::factory()->create([
            'is_featured' => false,
            'status'      => 'active',
            'starts_at'   => now()->subDay(),
            'ends_at'     => now()->addDay(),
        ]);

        $response = $this->get(route('frontend.campaigns.featured'));

        $response->assertStatus(200);
        $response->assertViewIs('campaigns.featured');
        $response->assertViewHas('campaigns');

        foreach ($featuredCampaigns as $campaign) {
            $response->assertSee($campaign->trans('name'));
        }
        $response->assertDontSee($regularCampaign->trans('name'));
    }

    public function test_can_view_campaigns_by_type(): void
    {
        $emailCampaigns = Campaign::factory()->count(3)->create([
            'type'      => 'email',
            'status'    => 'active',
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDay(),
        ]);

        $smsCampaign = Campaign::factory()->create([
            'type'      => 'sms',
            'status'    => 'active',
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDay(),
        ]);

        $response = $this->get(route('frontend.campaigns.by-type', 'email'));

        $response->assertStatus(200);
        $response->assertViewIs('campaigns.by-type');
        $response->assertViewHas('campaigns');
        $response->assertViewHas('type', 'email');

        foreach ($emailCampaigns as $campaign) {
            $response->assertSee($campaign->trans('name'));
        }
        $response->assertDontSee($smsCampaign->trans('name'));
    }

    public function test_only_active_campaigns_are_shown(): void
    {
        $activeCampaign = Campaign::factory()->create([
            'status'    => 'active',
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDay(),
        ]);

        $draftCampaign = Campaign::factory()->create(['status' => 'draft']);
        $expiredCampaign = Campaign::factory()->create([
            'status'  => 'active',
            'ends_at' => now()->subDay(),
        ]);

        $response = $this->get(route('frontend.campaigns.index'));

        $response->assertSee($activeCampaign->trans('name'));
        $response->assertDontSee($draftCampaign->trans('name'));
        $response->assertDontSee($expiredCampaign->trans('name'));
    }

    public function test_campaigns_are_sorted_by_priority(): void
    {
        $lowPriorityCampaign = Campaign::factory()->create([
            'display_priority' => 1,
            'status'           => 'active',
            'starts_at'        => now()->subDay(),
            'ends_at'          => now()->addDay(),
        ]);

        $highPriorityCampaign = Campaign::factory()->create([
            'display_priority' => 10,
            'status'           => 'active',
            'starts_at'        => now()->subDay(),
            'ends_at'          => now()->addDay(),
        ]);

        $response = $this->get(route('frontend.campaigns.index'));

        $response->assertStatus(200);

        // Check that high priority campaign appears first
        $content = $response->getContent();
        $highPriorityPosition = strpos($content, $highPriorityCampaign->trans('name'));
        $lowPriorityPosition = strpos($content, $lowPriorityCampaign->trans('name'));

        $this->assertTrue($highPriorityPosition < $lowPriorityPosition);
    }

    public function test_returns_404_for_inactive_campaign(): void
    {
        $inactiveCampaign = Campaign::factory()->create(['status' => 'draft']);

        $response = $this->get(route('frontend.campaigns.show', $inactiveCampaign));

        $response->assertStatus(404);
    }

    public function test_returns_404_for_expired_campaign(): void
    {
        $expiredCampaign = Campaign::factory()->create([
            'status'  => 'active',
            'ends_at' => now()->subDay(),
        ]);

        $response = $this->get(route('frontend.campaigns.show', $expiredCampaign));

        $response->assertStatus(404);
    }

    public function test_returns_404_for_future_campaign(): void
    {
        $futureCampaign = Campaign::factory()->create([
            'status'    => 'active',
            'starts_at' => now()->addDay(),
        ]);

        $response = $this->get(route('frontend.campaigns.show', $futureCampaign));

        $response->assertStatus(404);
    }

    public function test_campaign_show_page_includes_related_campaigns(): void
    {
        $category = Category::factory()->create();

        $mainCampaign = Campaign::factory()->create([
            'status'    => 'active',
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDay(),
        ]);
        $mainCampaign->targetCategories()->attach($category->id);

        $relatedCampaign = Campaign::factory()->create([
            'status'    => 'active',
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDay(),
        ]);
        $relatedCampaign->targetCategories()->attach($category->id);

        $unrelatedCampaign = Campaign::factory()->create([
            'status'    => 'active',
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDay(),
        ]);

        $response = $this->get(route('frontend.campaigns.show', $mainCampaign));

        $response->assertStatus(200);
        $response->assertSee($relatedCampaign->trans('name'));
        $response->assertDontSee($unrelatedCampaign->trans('name'));
    }

    public function test_campaign_click_requires_valid_campaign(): void
    {
        $response = $this->postJson(route('frontend.campaigns.click', 'invalid-slug'), [
            'type' => 'cta',
            'url'  => 'https://example.com',
        ]);

        $response->assertStatus(404);
    }

    public function test_campaign_conversion_requires_valid_campaign(): void
    {
        $response = $this->postJson(route('frontend.campaigns.conversion', 'invalid-slug'), [
            'type'  => 'purchase',
            'value' => 100.5,
        ]);

        $response->assertStatus(404);
    }

    public function test_campaign_click_requires_csrf_token(): void
    {
        $campaign = Campaign::factory()->create([
            'status'    => 'active',
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDay(),
        ]);

        $response = $this->post(route('frontend.campaigns.click', $campaign), [
            'type' => 'cta',
            'url'  => 'https://example.com',
        ]);

        $response->assertStatus(419);  // CSRF token mismatch
    }

    public function test_campaign_conversion_requires_csrf_token(): void
    {
        $campaign = Campaign::factory()->create([
            'status'    => 'active',
            'starts_at' => now()->subDay(),
            'ends_at'   => now()->addDay(),
        ]);

        $response = $this->post(route('frontend.campaigns.conversion', $campaign), [
            'type'  => 'purchase',
            'value' => 100.5,
        ]);

        $response->assertStatus(419);  // CSRF token mismatch
    }

    public function test_campaign_analytics_endpoint_returns_structured_marketing_insights(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 12));

        $campaignA = Campaign::factory()->create([
            'status'    => 'active',
            'starts_at' => now()->subDays(5),
            'ends_at'   => now()->addDays(5),
        ]);

        $campaignB = Campaign::factory()->create([
            'status'    => 'completed',
            'starts_at' => now()->subDays(3),
            'ends_at'   => now()->subDay(),
        ]);

        $legacyCampaign = Campaign::factory()->create([
            'status'    => 'active',
            'starts_at' => now()->subDays(30),
            'ends_at'   => now()->subDays(20),
        ]);

        $futureCampaign = Campaign::factory()->create([
            'status'    => 'scheduled',
            'starts_at' => now()->addDays(2),
            'ends_at'   => now()->addDays(10),
        ]);

        $campaignA->forceFill([
            'created_at'        => now()->subDays(3),
            'updated_at'        => now()->subDays(3),
            'total_views'       => 120,
            'total_clicks'      => 45,
            'total_conversions' => 12,
            'total_revenue'     => 640,
            'metadata'          => [
                'budget'            => 320,
                'total_views'       => 120,
                'total_clicks'      => 45,
                'total_conversions' => 12,
                'total_revenue'     => 640,
                'conversion_rate'   => 26.67,
            ],
        ])->save();

        $campaignB->forceFill([
            'created_at'        => now()->subDays(2),
            'updated_at'        => now()->subDays(2),
            'total_views'       => 60,
            'total_clicks'      => 18,
            'total_conversions' => 6,
            'total_revenue'     => 210,
            'metadata'          => [
                'budget'            => 150,
                'total_views'       => 60,
                'total_clicks'      => 18,
                'total_conversions' => 6,
                'total_revenue'     => 210,
                'conversion_rate'   => 33.33,
            ],
        ])->save();

        // Backdate and forward-date auxiliary campaigns to assert time window clamping.
        $legacyCampaign->forceFill([
            'created_at' => now()->subDays(30),
            'updated_at' => now()->subDays(30),
        ])->save();

        $futureCampaign->forceFill([
            'created_at' => now()->addDay(),
            'updated_at' => now()->addDay(),
        ])->save();

        CampaignConversion::withoutTimestamps(function () use ($campaignA, $campaignB): void {
            CampaignConversion::factory()->create([
                'campaign_id'               => $campaignA->id,
                'conversion_value'          => 320,
                'conversion_rate'           => 0.24,
                'attribution_model'         => 'last_click',
                'funnel_step'               => 'awareness',
                'conversion_path'           => ['touchpoints' => 3],
                'touchpoints'               => ['first_touch' => now()->subDays(4), 'last_touch' => now()->subDay()],
                'conversion_data'           => ['campaign_name' => 'Variant A'],
                'roi'                       => 1.8,
                'roas'                      => 2.6,
                'assisted_conversions'      => 2,
                'assisted_conversion_value' => 120,
                'is_verified'               => true,
                'is_attributed'             => true,
                'cost_per_conversion'       => 18,
                'time_on_site'              => 240,
                'page_views'                => 6,
                'converted_at'              => now()->subDays(1),
            ]);

            CampaignConversion::factory()->create([
                'campaign_id'               => $campaignA->id,
                'conversion_value'          => 210,
                'conversion_rate'           => 0.18,
                'attribution_model'         => 'first_click',
                'funnel_step'               => 'consideration',
                'conversion_path'           => ['touchpoints' => 2],
                'touchpoints'               => ['first_touch' => now()->subDays(5), 'last_touch' => now()->subHours(6)],
                'conversion_data'           => ['campaign_name' => 'Variant B'],
                'roi'                       => 1.2,
                'roas'                      => 1.9,
                'assisted_conversions'      => 1,
                'assisted_conversion_value' => 90,
                'is_verified'               => false,
                'is_attributed'             => true,
                'cost_per_conversion'       => 14,
                'time_on_site'              => 160,
                'page_views'                => 4,
                'converted_at'              => now()->subHours(12),
            ]);

            CampaignConversion::factory()->create([
                'campaign_id'               => $campaignB->id,
                'conversion_value'          => 150,
                'conversion_rate'           => 0.2,
                'attribution_model'         => 'linear',
                'funnel_step'               => 'purchase',
                'conversion_path'           => ['touchpoints' => 4],
                'touchpoints'               => ['first_touch' => now()->subDays(6), 'last_touch' => now()->subHours(2)],
                'conversion_data'           => ['campaign_name' => 'Variant A'],
                'roi'                       => 1.5,
                'roas'                      => 2.1,
                'assisted_conversions'      => 0,
                'assisted_conversion_value' => 0,
                'is_verified'               => true,
                'is_attributed'             => false,
                'cost_per_conversion'       => 12,
                'time_on_site'              => 200,
                'page_views'                => 5,
                'converted_at'              => now()->subHours(3),
            ]);

            // Conversion recorded outside the requested window should be ignored by analytics.
            CampaignConversion::factory()->create([
                'campaign_id'      => $campaignA->id,
                'conversion_value' => 275,
                'conversion_rate'  => 0.22,
                'converted_at'     => now()->subDays(10),
            ]);

            // Future-dated conversion must also be excluded from the audit window.
            CampaignConversion::factory()->create([
                'campaign_id'      => $campaignA->id,
                'conversion_value' => 500,
                'conversion_rate'  => 0.3,
                'converted_at'     => now()->addDay(),
            ]);
        });

        // Record supporting view and click telemetry so the analytics timeline has deterministic data points.
        CampaignView::factory()->count(3)->create([
            'campaign_id' => $campaignA->id,
            'viewed_at'   => now()->subDay(),
        ]);

        CampaignView::factory()->count(2)->create([
            'campaign_id' => $campaignB->id,
            'viewed_at'   => now()->subDays(2),
        ]);

        CampaignClick::factory()->count(4)->create([
            'campaign_id' => $campaignA->id,
            'clicked_at'  => now()->subDay(),
        ]);

        CampaignClick::factory()->create([
            'campaign_id' => $campaignB->id,
            'clicked_at'  => now()->subHours(6),
        ]);

        $response = $this->getJson(route('frontend.campaigns.api.analytics', ['period' => '7']));

        $response->assertOk();

        $payload = $response->json('data');

        // Assert the time window and engagement insights were calculated correctly.
        $this->assertSame(7, $payload['period']['days']);
        $this->assertSame('Last 7 days', $payload['period']['label']);
        $this->assertSame('2025-01-08', $payload['period']['start_date']);
        $this->assertSame('2025-01-15', $payload['period']['end_date']);
        $this->assertSame('day', $payload['period']['granularity']);
        $this->assertSame([
            '2025-01-08',
            '2025-01-09',
            '2025-01-10',
            '2025-01-11',
            '2025-01-12',
            '2025-01-13',
            '2025-01-14',
            '2025-01-15',
        ], $payload['period']['normalized_dates']);
        $this->assertSame(180, $payload['insights']['views_clicks']['metrics']['total_views']);
        $this->assertSame(63, $payload['insights']['views_clicks']['metrics']['total_clicks']);

        // Validate the aggregate counters clamp to the rolling period.
        $this->assertSame(2, $payload['totals']['campaigns_created']);
        $this->assertSame(2, $payload['totals']['campaigns_started']);
        $this->assertSame(1, $payload['totals']['campaigns_completed']);
        $this->assertSame(1, $payload['totals']['active_campaigns']);

        // Confirm conversion and ROI metrics are surfaced with attribution context.
        $this->assertSame(3, $payload['insights']['conversions']['metrics']['total_conversions']);
        $this->assertGreaterThan(0, $payload['insights']['conversions']['metrics']['average_conversion_rate']);
        $this->assertSame(2, $payload['insights']['conversions']['metrics']['verified_conversions']);
        $this->assertSame(2, $payload['insights']['conversions']['metrics']['attributed_conversions']);
        $this->assertEquals(210.0, $payload['insights']['conversions']['metrics']['assisted_conversion_value']);
        $this->assertEquals(470.0, $payload['insights']['roi_tracking']['metrics']['total_budget']);
        $this->assertGreaterThan(0, $payload['insights']['roi_tracking']['metrics']['roi_percentage']);

        // Ensure customer journey and multi-variant signals exist.
        $this->assertNotEmpty($payload['insights']['customer_journey']['metrics']['funnel_breakdown']);
        $this->assertSame(1, $payload['insights']['a_b_testing']['metrics']['multi_variant_campaigns']);
        $this->assertNotEmpty($payload['insights']['a_b_testing']['metrics']['variant_performance']);
        $this->assertFalse(collect($payload['insights']['a_b_testing']['metrics']['variant_performance'])->pluck('campaign_name')->contains('Campaign #0'));

        // Ensure the chart payload is normalized for each day of the requested period.
        $engagementChart = $payload['charts']['engagement_trend'];
        $this->assertSame([
            '2025-01-08',
            '2025-01-09',
            '2025-01-10',
            '2025-01-11',
            '2025-01-12',
            '2025-01-13',
            '2025-01-14',
            '2025-01-15',
        ], $engagementChart['normalized_dates']);
        $this->assertSame([0, 0, 0, 0, 0, 2, 3, 0], $engagementChart['datasets'][0]['data']);
        $this->assertSame([0, 0, 0, 0, 0, 0, 4, 1], $engagementChart['datasets'][1]['data']);
        $this->assertSame(5, $engagementChart['kpis']['total_views']);
        $this->assertSame(5, $engagementChart['kpis']['total_clicks']);

        $conversionChart = $payload['charts']['conversion_trend'];
        $this->assertSame([0, 0, 0, 0, 0, 0, 1, 2], $conversionChart['datasets'][0]['data']);
        $this->assertEquals([0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 320.0, 360.0], $conversionChart['datasets'][1]['data']);
        $this->assertSame(3, $conversionChart['kpis']['total_conversions']);
        $this->assertEquals(680.0, $conversionChart['kpis']['total_revenue']);

        Carbon::setTestNow();
    }
}
