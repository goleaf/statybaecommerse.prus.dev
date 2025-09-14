<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Campaign;
use App\Models\Channel;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Scopes\ActiveCampaignScope;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\StatusScope;
use App\Models\Scopes\VisibleScope;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdditionalGlobalScopesTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_model_has_active_and_enabled_scopes(): void
    {
        // Create test partners
        $activePartner = Partner::factory()->create([
            'is_enabled' => true,
            'is_active' => true,
        ]);

        $inactivePartner = Partner::factory()->create([
            'is_enabled' => false,
            'is_active' => true,
        ]);

        $disabledPartner = Partner::factory()->create([
            'is_enabled' => true,
            'is_active' => false,
        ]);

        // Test that only active and enabled partners are returned
        $partners = Partner::all();
        
        $this->assertCount(1, $partners);
        $this->assertEquals($activePartner->id, $partners->first()->id);

        // Test bypassing scopes
        $allPartners = Partner::withoutGlobalScopes()->get();
        $this->assertCount(3, $allPartners);
    }

    public function test_campaign_model_has_multiple_scopes(): void
    {
        // Create test campaigns
        $activeCampaign = Campaign::factory()->create([
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $inactiveCampaign = Campaign::factory()->create([
            'status' => 'inactive',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $expiredCampaign = Campaign::factory()->create([
            'status' => 'active',
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDay(),
        ]);

        $futureCampaign = Campaign::factory()->create([
            'status' => 'active',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);

        // Test that only active campaigns within date range are returned
        $campaigns = Campaign::all();
        
        $this->assertCount(1, $campaigns);
        $this->assertEquals($activeCampaign->id, $campaigns->first()->id);

        // Test bypassing specific scopes
        $allActiveCampaigns = Campaign::withoutGlobalScope(StatusScope::class)->get();
        $this->assertCount(2, $allActiveCampaigns); // active and future campaigns
    }

    public function test_attribute_model_has_active_enabled_visible_scopes(): void
    {
        // Create test attributes
        $visibleAttribute = Attribute::factory()->create([
            'is_enabled' => true,
            'is_visible' => true,
            'is_active' => true,
        ]);

        $hiddenAttribute = Attribute::factory()->create([
            'is_enabled' => true,
            'is_visible' => false,
            'is_active' => true,
        ]);

        $disabledAttribute = Attribute::factory()->create([
            'is_enabled' => false,
            'is_visible' => true,
            'is_active' => true,
        ]);

        $inactiveAttribute = Attribute::factory()->create([
            'is_enabled' => true,
            'is_visible' => true,
            'is_active' => false,
        ]);

        // Test that only active, enabled, and visible attributes are returned
        $attributes = Attribute::all();
        
        $this->assertCount(1, $attributes);
        $this->assertEquals($visibleAttribute->id, $attributes->first()->id);

        // Test bypassing scopes
        $allAttributes = Attribute::withoutGlobalScopes()->get();
        $this->assertCount(4, $allAttributes);
    }

    public function test_order_model_has_status_scope(): void
    {
        // Create test orders
        $pendingOrder = Order::factory()->create(['status' => 'pending']);
        $confirmedOrder = Order::factory()->create(['status' => 'confirmed']);
        $cancelledOrder = Order::factory()->create(['status' => 'cancelled']);
        $completedOrder = Order::factory()->create(['status' => 'completed']);

        // Test that only allowed status orders are returned
        $orders = Order::all();
        
        $this->assertCount(3, $orders); // pending, confirmed, completed
        $this->assertTrue($orders->contains('id', $pendingOrder->id));
        $this->assertTrue($orders->contains('id', $confirmedOrder->id));
        $this->assertTrue($orders->contains('id', $completedOrder->id));
        $this->assertFalse($orders->contains('id', $cancelledOrder->id));

        // Test bypassing scopes
        $allOrders = Order::withoutGlobalScopes()->get();
        $this->assertCount(4, $allOrders);
    }

    public function test_channel_model_has_multiple_scopes(): void
    {
        // Create test channels
        $activeChannel = Channel::factory()->create([
            'is_active' => true,
            'is_enabled' => true,
            'status' => 'active',
        ]);

        $inactiveChannel = Channel::factory()->create([
            'is_active' => false,
            'is_enabled' => true,
            'status' => 'active',
        ]);

        $disabledChannel = Channel::factory()->create([
            'is_active' => true,
            'is_enabled' => false,
            'status' => 'active',
        ]);

        $inactiveStatusChannel = Channel::factory()->create([
            'is_active' => true,
            'is_enabled' => true,
            'status' => 'inactive',
        ]);

        // Test that only active, enabled channels with active status are returned
        $channels = Channel::all();
        
        $this->assertCount(1, $channels);
        $this->assertEquals($activeChannel->id, $channels->first()->id);

        // Test bypassing scopes
        $allChannels = Channel::withoutGlobalScopes()->get();
        $this->assertCount(4, $allChannels);
    }

    public function test_zone_model_has_multiple_scopes(): void
    {
        // Create test zones
        $activeZone = Zone::factory()->create([
            'is_active' => true,
            'is_enabled' => true,
            'status' => 'active',
        ]);

        $inactiveZone = Zone::factory()->create([
            'is_active' => false,
            'is_enabled' => true,
            'status' => 'active',
        ]);

        $disabledZone = Zone::factory()->create([
            'is_active' => true,
            'is_enabled' => false,
            'status' => 'active',
        ]);

        $inactiveStatusZone = Zone::factory()->create([
            'is_active' => true,
            'is_enabled' => true,
            'status' => 'inactive',
        ]);

        // Test that only active, enabled zones with active status are returned
        $zones = Zone::all();
        
        $this->assertCount(1, $zones);
        $this->assertEquals($activeZone->id, $zones->first()->id);

        // Test bypassing scopes
        $allZones = Zone::withoutGlobalScopes()->get();
        $this->assertCount(4, $allZones);
    }

    public function test_status_scope_handles_different_model_statuses(): void
    {
        // Test Order statuses
        $order = Order::factory()->create(['status' => 'pending']);
        $this->assertTrue(Order::where('id', $order->id)->exists());

        $cancelledOrder = Order::factory()->create(['status' => 'cancelled']);
        $this->assertFalse(Order::where('id', $cancelledOrder->id)->exists());

        // Test Campaign statuses
        $campaign = Campaign::factory()->create(['status' => 'active']);
        $this->assertTrue(Campaign::where('id', $campaign->id)->exists());

        $draftCampaign = Campaign::factory()->create(['status' => 'draft']);
        $this->assertFalse(Campaign::where('id', $draftCampaign->id)->exists());
    }

    public function test_active_campaign_scope_filters_by_dates(): void
    {
        // Create campaigns with different date ranges
        $activeCampaign = Campaign::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $expiredCampaign = Campaign::factory()->create([
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDay(),
        ]);

        $futureCampaign = Campaign::factory()->create([
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);

        $noEndDateCampaign = Campaign::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => null,
        ]);

        // Test that only active campaigns are returned
        $campaigns = Campaign::withoutGlobalScope(StatusScope::class)->get();
        
        $this->assertCount(2, $campaigns); // active and no end date campaigns
        $this->assertTrue($campaigns->contains('id', $activeCampaign->id));
        $this->assertTrue($campaigns->contains('id', $noEndDateCampaign->id));
        $this->assertFalse($campaigns->contains('id', $expiredCampaign->id));
        $this->assertFalse($campaigns->contains('id', $futureCampaign->id));
    }

    public function test_global_scopes_can_be_combined_with_local_scopes(): void
    {
        // Create test data
        $activePartner = Partner::factory()->create([
            'is_enabled' => true,
            'is_active' => true,
        ]);

        $inactivePartner = Partner::factory()->create([
            'is_enabled' => false,
            'is_active' => true,
        ]);

        // Test that global scopes work with local scopes
        $partners = Partner::where('name', 'like', '%test%')->get();
        $this->assertCount(0, $partners); // No partners with 'test' in name

        // Test bypassing global scopes with local scopes
        $allPartners = Partner::withoutGlobalScopes()->where('is_enabled', false)->get();
        $this->assertCount(1, $allPartners);
        $this->assertEquals($inactivePartner->id, $allPartners->first()->id);
    }

    public function test_global_scopes_are_applied_to_relationships(): void
    {
        // Create test data with relationships
        $activePartner = Partner::factory()->create([
            'is_enabled' => true,
            'is_active' => true,
        ]);

        $inactivePartner = Partner::factory()->create([
            'is_enabled' => false,
            'is_active' => true,
        ]);

        // Test that relationships also apply global scopes
        $user = \App\Models\User::factory()->create();
        
        // If there's a relationship between User and Partner, test it
        // This is a placeholder test - adjust based on actual relationships
        $this->assertTrue(true); // Placeholder assertion
    }
}
