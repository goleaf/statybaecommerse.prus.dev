<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Campaign;
use App\Models\CampaignView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CampaignViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_view_can_be_created(): void
    {
        $campaign = Campaign::factory()->create();
        $customer = User::factory()->create();
        
        $view = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'session_id' => 'test_session_123',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'referer' => 'https://example.com',
            'customer_id' => $customer->id,
            'viewed_at' => now(),
        ]);

        $this->assertInstanceOf(CampaignView::class, $view);
        $this->assertEquals($campaign->id, $view->campaign_id);
        $this->assertEquals('test_session_123', $view->session_id);
        $this->assertEquals('192.168.1.1', $view->ip_address);
        $this->assertNotEmpty($view->user_agent);
        $this->assertEquals('https://example.com', $view->referer);
        $this->assertEquals($customer->id, $view->customer_id);
        $this->assertInstanceOf(\Carbon\Carbon::class, $view->viewed_at);
    }

    public function test_campaign_view_fillable_attributes(): void
    {
        $view = new CampaignView();
        $fillable = $view->getFillable();

        $expectedFillable = [
            'campaign_id',
            'session_id',
            'ip_address',
            'user_agent',
            'referer',
            'customer_id',
            'viewed_at',
        ];

        foreach ($expectedFillable as $field) {
            $this->assertContains($field, $fillable);
        }
    }

    public function test_campaign_view_casts(): void
    {
        $view = CampaignView::factory()->create([
            'viewed_at' => '2024-01-01 12:00:00',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $view->viewed_at);
    }

    public function test_campaign_view_belongs_to_campaign(): void
    {
        $campaign = Campaign::factory()->create();
        $view = CampaignView::factory()->create(['campaign_id' => $campaign->id]);

        $this->assertInstanceOf(Campaign::class, $view->campaign);
        $this->assertEquals($campaign->id, $view->campaign->id);
    }

    public function test_campaign_view_belongs_to_customer(): void
    {
        $customer = User::factory()->create();
        $view = CampaignView::factory()->create(['customer_id' => $customer->id]);

        $this->assertInstanceOf(User::class, $view->customer);
        $this->assertEquals($customer->id, $view->customer->id);
    }

    public function test_campaign_view_no_timestamps(): void
    {
        $view = new CampaignView();
        $this->assertFalse($view->timestamps);
    }

    public function test_campaign_view_basic_functionality(): void
    {
        $campaign = Campaign::factory()->create();
        $customer = User::factory()->create();
        
        $view = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'customer_id' => $customer->id,
            'session_id' => 'unique_session_456',
            'ip_address' => '10.0.0.1',
            'user_agent' => 'Custom User Agent',
            'referer' => 'https://google.com',
            'viewed_at' => now()->subHour(),
        ]);

        // Test basic model functionality
        $this->assertInstanceOf(CampaignView::class, $view);
        $this->assertEquals($campaign->id, $view->campaign_id);
        $this->assertEquals($customer->id, $view->customer_id);
        $this->assertEquals('unique_session_456', $view->session_id);
        $this->assertEquals('10.0.0.1', $view->ip_address);
        $this->assertEquals('Custom User Agent', $view->user_agent);
        $this->assertEquals('https://google.com', $view->referer);
    }

    public function test_campaign_view_table_name(): void
    {
        $view = new CampaignView();
        $this->assertEquals('campaign_views', $view->getTable());
    }

    public function test_campaign_view_factory(): void
    {
        $view = CampaignView::factory()->create();

        $this->assertInstanceOf(CampaignView::class, $view);
        $this->assertNotEmpty($view->campaign_id);
        $this->assertNotEmpty($view->session_id);
        $this->assertNotEmpty($view->ip_address);
        $this->assertNotEmpty($view->user_agent);
        $this->assertInstanceOf(\Carbon\Carbon::class, $view->viewed_at);
    }
}
