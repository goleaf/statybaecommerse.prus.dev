<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CampaignResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->actingAs(User::factory()->create());
    }

    public function test_can_create_campaign(): void
    {
        $campaignData = [
            'name' => 'Test Campaign',
            'slug' => 'test-campaign',
            'description' => 'Test campaign description',
            'type' => 'email',
            'status' => 'draft',
            'is_active' => true,
        ];

        $campaign = Campaign::create($campaignData);

        $this->assertDatabaseHas('campaigns', [
            'name' => json_encode(['lt' => 'Test Campaign']),
            'slug' => 'test-campaign',
            'type' => 'email',
            'status' => 'draft',
        ]);

        $this->assertEquals('Test Campaign', $campaign->name);
        $this->assertEquals('email', $campaign->type);
        $this->assertEquals('draft', $campaign->status);
    }

    public function test_can_update_campaign(): void
    {
        $campaign = Campaign::factory()->create();

        $campaign->update([
            'name' => 'Updated Campaign Name',
            'description' => 'Updated description',
            'status' => 'running',
        ]);

        $this->assertEquals('Updated Campaign Name', $campaign->getTranslation('name', 'lt'));
        $this->assertEquals('Updated description', $campaign->getTranslation('description', 'lt'));
        $this->assertEquals('running', $campaign->status);
    }

    public function test_can_delete_campaign(): void
    {
        $campaign = Campaign::factory()->create();

        $campaign->delete();

        $this->assertSoftDeleted('campaigns', [
            'id' => $campaign->id,
        ]);
    }

    public function test_can_filter_campaigns_by_type(): void
    {
        Campaign::factory()->create(['type' => 'email']);
        Campaign::factory()->create(['type' => 'sms']);

        $emailCampaigns = Campaign::where('type', 'email')->get();
        $smsCampaigns = Campaign::where('type', 'sms')->get();

        $this->assertCount(1, $emailCampaigns);
        $this->assertCount(1, $smsCampaigns);
        $this->assertEquals('email', $emailCampaigns->first()->type);
        $this->assertEquals('sms', $smsCampaigns->first()->type);
    }

    public function test_can_filter_campaigns_by_status(): void
    {
        Campaign::factory()->create(['status' => 'running']);
        Campaign::factory()->create(['status' => 'paused']);

        $runningCampaigns = Campaign::where('status', 'running')->get();
        $pausedCampaigns = Campaign::where('status', 'paused')->get();

        $this->assertCount(1, $runningCampaigns);
        $this->assertCount(1, $pausedCampaigns);
        $this->assertEquals('running', $runningCampaigns->first()->status);
        $this->assertEquals('paused', $pausedCampaigns->first()->status);
    }

    public function test_can_filter_campaigns_by_active_status(): void
    {
        Campaign::factory()->create(['is_active' => true]);
        Campaign::factory()->create(['is_active' => false]);

        $activeCampaigns = Campaign::where('is_active', true)->get();
        $inactiveCampaigns = Campaign::where('is_active', false)->get();

        $this->assertCount(1, $activeCampaigns);
        $this->assertCount(1, $inactiveCampaigns);
        $this->assertTrue($activeCampaigns->first()->is_active);
        $this->assertFalse($inactiveCampaigns->first()->is_active);
    }
}