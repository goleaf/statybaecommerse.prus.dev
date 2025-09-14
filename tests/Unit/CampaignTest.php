<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Campaign;
use App\Models\Channel;
use App\Models\Zone;
use App\Models\Translations\CampaignTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CampaignTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_can_be_created(): void
    {
        $campaign = Campaign::factory()->create([
            'name' => 'Test Campaign',
            'slug' => 'test-campaign',
            'status' => 'active',
            'is_featured' => true,
            'send_notifications' => true,
            'track_conversions' => true,
            'max_uses' => 100,
            'budget_limit' => 1000.00,
        ]);

        $this->assertInstanceOf(Campaign::class, $campaign);
        $this->assertEquals('Test Campaign', $campaign->name);
        $this->assertEquals('test-campaign', $campaign->slug);
        $this->assertEquals('active', $campaign->status);
        $this->assertTrue($campaign->is_featured);
        $this->assertTrue($campaign->send_notifications);
        $this->assertTrue($campaign->track_conversions);
        $this->assertEquals(100, $campaign->max_uses);
        $this->assertEquals(1000.00, $campaign->budget_limit);
    }

    public function test_campaign_fillable_attributes(): void
    {
        $campaign = new Campaign();
        $fillable = $campaign->getFillable();

        $expectedFillable = [
            'name',
            'slug',
            'starts_at',
            'ends_at',
            'channel_id',
            'zone_id',
            'status',
            'metadata',
            'is_featured',
            'send_notifications',
            'track_conversions',
            'max_uses',
            'budget_limit',
        ];

        foreach ($expectedFillable as $field) {
            $this->assertContains($field, $fillable);
        }
    }

    public function test_campaign_casts(): void
    {
        $campaign = Campaign::factory()->create([
            'starts_at' => '2024-01-01 00:00:00',
            'ends_at' => '2024-12-31 23:59:59',
            'is_featured' => true,
            'send_notifications' => false,
            'track_conversions' => true,
            'max_uses' => 50,
            'budget_limit' => 500.00,
            'metadata' => ['key' => 'value'],
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $campaign->starts_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $campaign->ends_at);
        $this->assertTrue($campaign->is_featured);
        $this->assertFalse($campaign->send_notifications);
        $this->assertTrue($campaign->track_conversions);
        $this->assertIsInt($campaign->max_uses);
        $this->assertIsFloat($campaign->budget_limit);
        $this->assertIsArray($campaign->metadata);
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

    public function test_campaign_has_translations(): void
    {
        $campaign = Campaign::factory()->create();
        
        $this->assertTrue(method_exists($campaign, 'translations'));
        $this->assertTrue(method_exists($campaign, 'trans'));
    }

    public function test_campaign_translation_methods(): void
    {
        $campaign = Campaign::factory()->create([
            'name' => 'Test Campaign',
        ]);

        // Create a translation
        $translation = CampaignTranslation::factory()->create([
            'campaign_id' => $campaign->id,
            'locale' => 'lt',
            'name' => 'Testo Kampanija',
        ]);

        $this->assertEquals('Testo Kampanija', $campaign->getTranslatedName('lt'));
        $this->assertEquals('Test Campaign', $campaign->getTranslatedName('en'));
    }

    public function test_campaign_scopes(): void
    {
        // Create campaigns with different statuses
        $activeCampaign = Campaign::factory()->create(['status' => 'active']);
        $inactiveCampaign = Campaign::factory()->create(['status' => 'inactive']);
        $featuredCampaign = Campaign::factory()->create(['is_featured' => true]);
        $regularCampaign = Campaign::factory()->create(['is_featured' => false]);

        // Test active scope
        $activeCampaigns = Campaign::active()->get();
        $this->assertTrue($activeCampaigns->contains($activeCampaign));
        $this->assertFalse($activeCampaigns->contains($inactiveCampaign));

        // Test featured scope
        $featuredCampaigns = Campaign::featured()->get();
        $this->assertTrue($featuredCampaigns->contains($featuredCampaign));
        $this->assertFalse($featuredCampaigns->contains($regularCampaign));
    }

    public function test_campaign_status_methods(): void
    {
        $activeCampaign = Campaign::factory()->create(['status' => 'active']);
        $inactiveCampaign = Campaign::factory()->create(['status' => 'inactive']);

        $this->assertTrue($activeCampaign->isActive());
        $this->assertFalse($activeCampaign->isInactive());
        $this->assertFalse($inactiveCampaign->isActive());
        $this->assertTrue($inactiveCampaign->isInactive());
    }

    public function test_campaign_date_methods(): void
    {
        $now = now();
        $pastCampaign = Campaign::factory()->create([
            'starts_at' => $now->copy()->subDays(10),
            'ends_at' => $now->copy()->subDays(1),
        ]);
        
        $currentCampaign = Campaign::factory()->create([
            'starts_at' => $now->copy()->subDays(1),
            'ends_at' => $now->copy()->addDays(1),
        ]);
        
        $futureCampaign = Campaign::factory()->create([
            'starts_at' => $now->copy()->addDays(1),
            'ends_at' => $now->copy()->addDays(10),
        ]);

        $this->assertTrue($pastCampaign->isExpired());
        $this->assertFalse($pastCampaign->isActive());
        $this->assertFalse($pastCampaign->isUpcoming());

        $this->assertFalse($currentCampaign->isExpired());
        $this->assertTrue($currentCampaign->isActive());
        $this->assertFalse($currentCampaign->isUpcoming());

        $this->assertFalse($futureCampaign->isExpired());
        $this->assertFalse($futureCampaign->isActive());
        $this->assertTrue($futureCampaign->isUpcoming());
    }

    public function test_campaign_uses_soft_deletes(): void
    {
        $campaign = Campaign::factory()->create();
        $campaignId = $campaign->id;

        $campaign->delete();

        $this->assertSoftDeleted('discount_campaigns', ['id' => $campaignId]);
        $this->assertNull(Campaign::find($campaignId));
        $this->assertNotNull(Campaign::withTrashed()->find($campaignId));
    }

    public function test_campaign_table_name(): void
    {
        $campaign = new Campaign();
        $this->assertEquals('discount_campaigns', $campaign->getTable());
    }

    public function test_campaign_route_key_name(): void
    {
        $campaign = new Campaign();
        $this->assertEquals('slug', $campaign->getRouteKeyName());
    }

    public function test_campaign_factory(): void
    {
        $campaign = Campaign::factory()->create();

        $this->assertInstanceOf(Campaign::class, $campaign);
        $this->assertNotEmpty($campaign->name);
        $this->assertNotEmpty($campaign->slug);
        $this->assertNotEmpty($campaign->status);
    }
}
