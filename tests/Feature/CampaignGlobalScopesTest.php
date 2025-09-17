<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\AnalyticsEvent;
use App\Models\CampaignClick;
use App\Models\CampaignConversion;
use App\Models\CampaignCustomerSegment;
use App\Models\CampaignProductTarget;
use App\Models\CampaignSchedule;
use App\Models\CampaignView;
use App\Models\User;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\StatusScope;
use App\Models\Scopes\UserOwnedScope;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class CampaignGlobalScopesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_model_has_active_scope(): void
    {
        // Create test admin users
        $activeAdmin = AdminUser::factory()->create(['is_active' => true]);
        $inactiveAdmin = AdminUser::factory()->create(['is_active' => false]);

        // Test that only active admin users are returned
        $admins = AdminUser::all();
        
        $this->assertCount(1, $admins);
        $this->assertEquals($activeAdmin->id, $admins->first()->id);

        // Test bypassing scopes
        $allAdmins = AdminUser::withoutGlobalScopes()->get();
        $this->assertCount(2, $allAdmins);
    }

    public function test_analytics_event_model_has_user_owned_scope(): void
    {
        // Create test users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create test analytics events
        $user1Event = AnalyticsEvent::factory()->create(['user_id' => $user1->id]);
        $user2Event = AnalyticsEvent::factory()->create(['user_id' => $user2->id]);

        // Test that only current user's events are returned
        $this->actingAs($user1);
        $events = AnalyticsEvent::all();
        
        $this->assertCount(1, $events);
        $this->assertEquals($user1Event->id, $events->first()->id);

        // Test bypassing scopes
        $allEvents = AnalyticsEvent::withoutGlobalScopes()->get();
        $this->assertCount(2, $allEvents);
    }

    public function test_campaign_click_model_has_active_scope(): void
    {
        // Create test campaign clicks
        $activeClick = CampaignClick::factory()->create(['is_active' => true]);
        $inactiveClick = CampaignClick::factory()->create(['is_active' => false]);

        // Test that only active clicks are returned
        $clicks = CampaignClick::all();
        
        $this->assertCount(1, $clicks);
        $this->assertEquals($activeClick->id, $clicks->first()->id);

        // Test bypassing scopes
        $allClicks = CampaignClick::withoutGlobalScopes()->get();
        $this->assertCount(2, $allClicks);
    }

    public function test_campaign_conversion_model_has_multiple_scopes(): void
    {
        // Create test campaign conversions
        $activeConversion = CampaignConversion::factory()->create([
            'is_active' => true,
            'status' => 'completed',
        ]);

        $inactiveConversion = CampaignConversion::factory()->create([
            'is_active' => false,
            'status' => 'completed',
        ]);

        $pendingConversion = CampaignConversion::factory()->create([
            'is_active' => true,
            'status' => 'pending',
        ]);

        // Test that only active conversions with allowed status are returned
        $conversions = CampaignConversion::all();
        
        $this->assertCount(1, $conversions);
        $this->assertEquals($activeConversion->id, $conversions->first()->id);

        // Test bypassing scopes
        $allConversions = CampaignConversion::withoutGlobalScopes()->get();
        $this->assertCount(3, $allConversions);
    }

    public function test_campaign_customer_segment_model_has_active_scope(): void
    {
        // Create test campaign customer segments
        $activeSegment = CampaignCustomerSegment::factory()->create(['is_active' => true]);
        $inactiveSegment = CampaignCustomerSegment::factory()->create(['is_active' => false]);

        // Test that only active segments are returned
        $segments = CampaignCustomerSegment::all();
        
        $this->assertCount(1, $segments);
        $this->assertEquals($activeSegment->id, $segments->first()->id);

        // Test bypassing scopes
        $allSegments = CampaignCustomerSegment::withoutGlobalScopes()->get();
        $this->assertCount(2, $allSegments);
    }

    public function test_campaign_product_target_model_has_active_scope(): void
    {
        // Create test campaign product targets
        $activeTarget = CampaignProductTarget::factory()->create(['is_active' => true]);
        $inactiveTarget = CampaignProductTarget::factory()->create(['is_active' => false]);

        // Test that only active targets are returned
        $targets = CampaignProductTarget::all();
        
        $this->assertCount(1, $targets);
        $this->assertEquals($activeTarget->id, $targets->first()->id);

        // Test bypassing scopes
        $allTargets = CampaignProductTarget::withoutGlobalScopes()->get();
        $this->assertCount(2, $allTargets);
    }

    public function test_campaign_schedule_model_has_active_scope(): void
    {
        // Create test campaign schedules
        $activeSchedule = CampaignSchedule::factory()->create(['is_active' => true]);
        $inactiveSchedule = CampaignSchedule::factory()->create(['is_active' => false]);

        // Test that only active schedules are returned
        $schedules = CampaignSchedule::all();
        
        $this->assertCount(1, $schedules);
        $this->assertEquals($activeSchedule->id, $schedules->first()->id);

        // Test bypassing scopes
        $allSchedules = CampaignSchedule::withoutGlobalScopes()->get();
        $this->assertCount(2, $allSchedules);
    }

    public function test_campaign_view_model_has_active_scope(): void
    {
        // Create test campaign views
        $activeView = CampaignView::factory()->create(['is_active' => true]);
        $inactiveView = CampaignView::factory()->create(['is_active' => false]);

        // Test that only active views are returned
        $views = CampaignView::all();
        
        $this->assertCount(1, $views);
        $this->assertEquals($activeView->id, $views->first()->id);

        // Test bypassing scopes
        $allViews = CampaignView::withoutGlobalScopes()->get();
        $this->assertCount(2, $allViews);
    }

    public function test_global_scopes_can_be_combined_with_local_scopes(): void
    {
        // Create test data
        $user = User::factory()->create();
        $event = AnalyticsEvent::factory()->create(['user_id' => $user->id]);

        // Test that global scopes work with local scopes
        $this->actingAs($user);
        $events = AnalyticsEvent::where('event_type', 'like', '%test%')->get();
        $this->assertCount(0, $events); // No events with 'test' in event_type

        // Test bypassing global scopes with local scopes
        $allEvents = AnalyticsEvent::withoutGlobalScopes()->where('user_id', $user->id)->get();
        $this->assertCount(1, $allEvents);
        $this->assertEquals($event->id, $allEvents->first()->id);
    }

    public function test_global_scopes_are_applied_to_relationships(): void
    {
        // Create test data with relationships
        $user = User::factory()->create();
        $event = AnalyticsEvent::factory()->create(['user_id' => $user->id]);

        // Test that relationships also apply global scopes
        $this->actingAs($user);
        $userEvents = $user->analyticsEvents;
        $this->assertCount(1, $userEvents);
        $this->assertEquals($event->id, $userEvents->first()->id);
    }

    public function test_user_owned_scope_works_without_authentication(): void
    {
        // Create test data
        $user = User::factory()->create();
        $event = AnalyticsEvent::factory()->create(['user_id' => $user->id]);

        // Test without authentication
        $events = AnalyticsEvent::all();
        $this->assertCount(0, $events); // No events returned without auth

        // Test with authentication
        $this->actingAs($user);
        $events = AnalyticsEvent::all();
        $this->assertCount(1, $events);
        $this->assertEquals($event->id, $events->first()->id);
    }

    public function test_campaign_conversion_scope_combinations(): void
    {
        // Test different combinations of conversion scopes
        $conversion1 = CampaignConversion::factory()->create([
            'is_active' => true,
            'status' => 'completed',
        ]);

        $conversion2 = CampaignConversion::factory()->create([
            'is_active' => false,
            'status' => 'completed',
        ]);

        $conversion3 = CampaignConversion::factory()->create([
            'is_active' => true,
            'status' => 'pending',
        ]);

        // Test bypassing specific scopes
        $activeConversions = CampaignConversion::withoutGlobalScope(StatusScope::class)->get();
        $this->assertCount(1, $activeConversions); // Only active conversions

        $completedConversions = CampaignConversion::withoutGlobalScope(ActiveScope::class)->get();
        $this->assertCount(1, $completedConversions); // Only completed conversions
    }
}
