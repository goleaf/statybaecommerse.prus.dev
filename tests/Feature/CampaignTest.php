<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Category;
use App\Models\Channel;
use App\Models\CustomerGroup;
use App\Models\Product;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CampaignTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_index_page_loads(): void
    {
        $campaigns = Campaign::factory()->count(3)->active()->create();

        $response = $this->get(route('frontend.campaigns.index'));

        $response->assertStatus(200);
        $response->assertViewIs('campaigns.index');
        $response->assertViewHas('campaigns');
    }

    public function test_campaign_index_page_filters_by_type(): void
    {
        $emailCampaigns = Campaign::factory()->count(2)->email()->active()->create();
        $bannerCampaigns = Campaign::factory()->count(2)->banner()->active()->create();

        $response = $this->get(route('frontend.campaigns.index', ['type' => 'email']));

        $response->assertStatus(200);
        $response->assertViewHas('campaigns');
    }

    public function test_campaign_index_page_filters_by_category(): void
    {
        $category = Category::factory()->create();
        $campaigns = Campaign::factory()->count(2)->active()->create();
        $campaigns->each->targetCategories()->attach($category->id);

        $response = $this->get(route('frontend.campaigns.index', ['category' => $category->slug]));

        $response->assertStatus(200);
        $response->assertViewHas('campaigns');
    }

    public function test_campaign_index_page_searches_by_name(): void
    {
        $campaign = Campaign::factory()->create(['name' => 'Special Campaign']);
        Campaign::factory()->count(2)->create(['name' => 'Regular Campaign']);

        $response = $this->get(route('frontend.campaigns.index', ['search' => 'Special']));

        $response->assertStatus(200);
        $response->assertViewHas('campaigns');
    }

    public function test_campaign_show_page_loads(): void
    {
        $campaign = Campaign::factory()->active()->create();

        $response = $this->get(route('frontend.campaigns.show', $campaign));

        $response->assertStatus(200);
        $response->assertViewIs('campaigns.show');
        $response->assertViewHas('campaign');
        $response->assertViewHas('relatedCampaigns');
    }

    public function test_campaign_show_page_records_view(): void
    {
        $campaign = Campaign::factory()->create(['total_views' => 0]);

        $this->get(route('frontend.campaigns.show', $campaign));

        $this->assertEquals(1, $campaign->fresh()->total_views);
    }

    public function test_campaign_click_endpoint(): void
    {
        $campaign = Campaign::factory()->create(['total_clicks' => 0]);

        $response = $this->post(route('frontend.campaigns.click', $campaign), [
            'type' => 'cta',
            'url' => 'https://example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertEquals(1, $campaign->fresh()->total_clicks);
    }

    public function test_campaign_conversion_endpoint(): void
    {
        $campaign = Campaign::factory()->create([
            'total_conversions' => 0,
            'total_revenue' => 0,
        ]);

        $response = $this->post(route('frontend.campaigns.conversion', $campaign), [
            'type' => 'purchase',
            'value' => 100.50,
            'order_id' => 123,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $freshCampaign = $campaign->fresh();
        $this->assertEquals(1, $freshCampaign->total_conversions);
        $this->assertEquals(100.50, $freshCampaign->total_revenue);
    }

    public function test_campaign_featured_page_loads(): void
    {
        $featuredCampaigns = Campaign::factory()->count(3)->featured()->active()->create();

        $response = $this->get(route('frontend.campaigns.featured'));

        $response->assertStatus(200);
        $response->assertViewIs('campaigns.featured');
        $response->assertViewHas('campaigns');
    }

    public function test_campaign_by_type_page_loads(): void
    {
        $emailCampaigns = Campaign::factory()->count(3)->email()->active()->create();

        $response = $this->get(route('frontend.campaigns.by-type', 'email'));

        $response->assertStatus(200);
        $response->assertViewIs('campaigns.by-type');
        $response->assertViewHas('campaigns');
        $response->assertViewHas('type', 'email');
    }

    public function test_campaign_search_page_loads(): void
    {
        $campaigns = Campaign::factory()->count(3)->active()->create();

        $response = $this->get(route('frontend.campaigns.search', ['q' => 'test']));

        $response->assertStatus(200);
        $response->assertViewIs('campaigns.search');
        $response->assertViewHas('campaigns');
        $response->assertViewHas('query', 'test');
    }

    public function test_campaign_statistics_api_endpoint(): void
    {
        Campaign::factory()->count(3)->active()->create();
        Campaign::factory()->count(2)->scheduled()->create();

        $response = $this->get(route('frontend.campaigns.api.statistics'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'total_campaigns',
                'active_campaigns',
                'scheduled_campaigns',
                'completed_campaigns',
                'total_views',
                'total_clicks',
                'total_conversions',
                'total_revenue',
                'average_conversion_rate',
                'average_click_through_rate',
                'average_roi',
            ],
        ]);
    }

    public function test_campaign_types_api_endpoint(): void
    {
        Campaign::factory()->count(2)->email()->create();
        Campaign::factory()->count(3)->banner()->create();

        $response = $this->get(route('frontend.campaigns.api.types'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'type',
                    'label',
                    'count',
                    'icon',
                    'color',
                ],
            ],
        ]);
    }

    public function test_campaign_performance_api_endpoint(): void
    {
        Campaign::factory()->count(2)->highPerformance()->create();
        Campaign::factory()->count(3)->lowPerformance()->create();

        $response = $this->get(route('frontend.campaigns.api.performance'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'high_performing',
                'medium_performing',
                'low_performing',
                'needs_attention',
            ],
        ]);
    }

    public function test_campaign_analytics_api_endpoint(): void
    {
        Campaign::factory()->count(5)->create();

        $response = $this->get(route('frontend.campaigns.api.analytics', ['period' => '30']));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'period',
                'start_date',
                'end_date',
                'campaigns_created',
                'campaigns_started',
                'campaigns_completed',
                'total_views',
                'total_clicks',
                'total_conversions',
                'total_revenue',
                'top_performing_campaigns',
                'campaign_types_breakdown',
            ],
        ]);
    }

    public function test_campaign_comparison_api_endpoint(): void
    {
        $campaigns = Campaign::factory()->count(3)->create();

        $response = $this->get(route('frontend.campaigns.api.compare', [
            'campaign_ids' => $campaigns->pluck('id')->toArray(),
        ]));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'type',
                    'type_label',
                    'status',
                    'status_label',
                    'views',
                    'clicks',
                    'conversions',
                    'revenue',
                    'conversion_rate',
                    'click_through_rate',
                    'roi',
                    'performance_score',
                    'performance_grade',
                    'budget',
                    'budget_utilization',
                ],
            ],
        ]);
    }

    public function test_campaign_comparison_api_endpoint_without_campaigns(): void
    {
        $response = $this->get(route('frontend.campaigns.api.compare'));

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_campaign_recommendations_api_endpoint(): void
    {
        $campaign = Campaign::factory()->create([
            'total_views' => 1000,
            'total_clicks' => 5,
            'total_conversions' => 1,
            'budget_limit' => 1000,
            'total_revenue' => 950,
            'end_date' => now()->addDays(3),
            'cta_text' => null,
            'content' => 'Short',
        ]);

        $response = $this->get(route('frontend.campaigns.recommendations', $campaign));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'type',
                    'priority',
                    'title',
                    'description',
                    'action',
                ],
            ],
        ]);
    }

    public function test_campaign_show_page_with_related_campaigns(): void
    {
        $category = Category::factory()->create();
        $mainCampaign = Campaign::factory()->active()->create();
        $mainCampaign->targetCategories()->attach($category->id);

        $relatedCampaigns = Campaign::factory()->count(3)->active()->create();
        $relatedCampaigns->each->targetCategories()->attach($category->id);

        $unrelatedCampaign = Campaign::factory()->active()->create();

        $response = $this->get(route('frontend.campaigns.show', $mainCampaign));

        $response->assertStatus(200);
        $response->assertViewHas('relatedCampaigns');
        
        $viewData = $response->viewData('relatedCampaigns');
        $this->assertCount(3, $viewData);
        $this->assertFalse($viewData->contains($mainCampaign));
        $this->assertFalse($viewData->contains($unrelatedCampaign));
    }

    public function test_campaign_show_page_loads_relationships(): void
    {
        $channel = Channel::factory()->create();
        $zone = Zone::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $customerGroup = CustomerGroup::factory()->create();

        $campaign = Campaign::factory()->create([
            'channel_id' => $channel->id,
            'zone_id' => $zone->id,
        ]);

        $campaign->targetCategories()->attach($category->id);
        $campaign->targetProducts()->attach($product->id);
        $campaign->targetCustomerGroups()->attach($customerGroup->id);

        $response = $this->get(route('frontend.campaigns.show', $campaign));

        $response->assertStatus(200);
        $response->assertViewHas('campaign');
        
        $viewCampaign = $response->viewData('campaign');
        $this->assertTrue($viewCampaign->relationLoaded('targetCategories'));
        $this->assertTrue($viewCampaign->relationLoaded('targetProducts'));
        $this->assertTrue($viewCampaign->relationLoaded('targetCustomerGroups'));
        $this->assertTrue($viewCampaign->relationLoaded('channel'));
        $this->assertTrue($viewCampaign->relationLoaded('zone'));
        $this->assertTrue($viewCampaign->relationLoaded('discounts'));
    }

    public function test_campaign_click_with_authenticated_user(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['total_clicks' => 0]);

        $response = $this->actingAs($user)->post(route('frontend.campaigns.click', $campaign), [
            'type' => 'cta',
            'url' => 'https://example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertEquals(1, $campaign->fresh()->total_clicks);
    }

    public function test_campaign_conversion_with_authenticated_user(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create([
            'total_conversions' => 0,
            'total_revenue' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('frontend.campaigns.conversion', $campaign), [
            'type' => 'purchase',
            'value' => 250.75,
            'order_id' => 456,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $freshCampaign = $campaign->fresh();
        $this->assertEquals(1, $freshCampaign->total_conversions);
        $this->assertEquals(250.75, $freshCampaign->total_revenue);
    }
}
