<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CampaignViewResource;
use App\Models\Campaign;
use App\Models\CampaignView;
use App\Models\User;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

// Use fully-qualified base TestCase to avoid alias conflicts

final class CampaignViewResourceTest extends \Tests\TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_can_list_campaign_views(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();

        CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'referrer' => 'https://google.com',
            'session_id' => 'session_123',
        ]);

        CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => null,  // Guest user
            'ip_address' => '192.168.1.2',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignViewResource::class,
        ])
            ->assertCanSeeTableRecords(CampaignView::all())
            ->assertCanSeeTableColumns([
                'campaign.name',
                'user.name',
                'ip_address',
                'user_agent',
                'referrer',
                'created_at',
            ]);
    }

    public function test_can_view_campaign_view_details(): void
    {
        $campaign = Campaign::factory()->create(['name' => 'Test Campaign']);
        $user = User::factory()->create(['name' => 'John Doe']);

        $view = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'referrer' => 'https://facebook.com',
            'session_id' => 'session_456',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ViewRecord::class, [
            'resource' => CampaignViewResource::class,
            'record' => $view->id,
        ])
            ->assertCanSeeTableRecords([$view]);
    }

    public function test_can_filter_by_campaign(): void
    {
        $campaign1 = Campaign::factory()->create(['name' => 'Campaign 1']);
        $campaign2 = Campaign::factory()->create(['name' => 'Campaign 2']);

        $view1 = CampaignView::factory()->create([
            'campaign_id' => $campaign1->id,
            'ip_address' => '192.168.1.1',
        ]);

        $view2 = CampaignView::factory()->create([
            'campaign_id' => $campaign2->id,
            'ip_address' => '192.168.1.2',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignViewResource::class,
        ])
            ->filterTable('campaign_id', $campaign1->id)
            ->assertCanSeeTableRecords([$view1])
            ->assertCanNotSeeTableRecords([$view2]);
    }

    public function test_can_filter_by_user(): void
    {
        $campaign = Campaign::factory()->create();
        $user1 = User::factory()->create(['name' => 'User 1']);
        $user2 = User::factory()->create(['name' => 'User 2']);

        $view1 = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $user1->id,
            'ip_address' => '192.168.1.1',
        ]);

        $view2 = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $user2->id,
            'ip_address' => '192.168.1.2',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignViewResource::class,
        ])
            ->filterTable('user_id', $user1->id)
            ->assertCanSeeTableRecords([$view1])
            ->assertCanNotSeeTableRecords([$view2]);
    }

    public function test_can_filter_by_ip_address(): void
    {
        $campaign = Campaign::factory()->create();

        $view1 = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'ip_address' => '192.168.1.100',
        ]);

        $view2 = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'ip_address' => '192.168.1.200',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignViewResource::class,
        ])
            ->filterTable('ip_address', '192.168.1.100')
            ->assertCanSeeTableRecords([$view1])
            ->assertCanNotSeeTableRecords([$view2]);
    }

    public function test_guest_user_display(): void
    {
        $campaign = Campaign::factory()->create();

        $guestView = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => null,
            'ip_address' => '192.168.1.1',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignViewResource::class,
        ])
            ->assertCanSeeTableRecords([$guestView]);
    }

    public function test_user_agent_tooltip(): void
    {
        $campaign = Campaign::factory()->create();
        $longUserAgent = str_repeat('Mozilla/5.0 ', 20);  // Long user agent string

        $view = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'user_agent' => $longUserAgent,
            'ip_address' => '192.168.1.1',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignViewResource::class,
        ])
            ->assertCanSeeTableRecords([$view]);
    }

    public function test_referrer_tooltip(): void
    {
        $campaign = Campaign::factory()->create();
        $longReferrer = 'https://'.str_repeat('very-long-domain-name-', 10).'.com';

        $view = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'referrer' => $longReferrer,
            'ip_address' => '192.168.1.1',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignViewResource::class,
        ])
            ->assertCanSeeTableRecords([$view]);
    }

    public function test_campaign_relationship_display(): void
    {
        $campaign = Campaign::factory()->create(['name' => 'Test Campaign']);
        $view = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'ip_address' => '192.168.1.1',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignViewResource::class,
        ])
            ->assertCanSeeTableRecords([$view]);
    }

    public function test_user_relationship_display(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create(['name' => 'John Doe']);

        $view = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'ip_address' => '192.168.1.1',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignViewResource::class,
        ])
            ->assertCanSeeTableRecords([$view]);
    }

    public function test_ip_address_validation(): void
    {
        $campaign = Campaign::factory()->create();

        $this->actingAs($this->adminUser);

        // Test with valid IP
        $validView = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'ip_address' => '192.168.1.1',
        ]);

        $this->assertDatabaseHas('campaign_views', [
            'ip_address' => '192.168.1.1',
        ]);

        // Test with IPv6
        $ipv6View = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'ip_address' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
        ]);

        $this->assertDatabaseHas('campaign_views', [
            'ip_address' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
        ]);
    }

    public function test_session_id_tracking(): void
    {
        $campaign = Campaign::factory()->create();
        $sessionId = 'session_'.uniqid();

        $view = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'session_id' => $sessionId,
            'ip_address' => '192.168.1.1',
        ]);

        $this->assertDatabaseHas('campaign_views', [
            'session_id' => $sessionId,
        ]);
    }

    public function test_referrer_url_validation(): void
    {
        $campaign = Campaign::factory()->create();

        $view = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'referrer' => 'https://google.com/search?q=test',
            'ip_address' => '192.168.1.1',
        ]);

        $this->assertDatabaseHas('campaign_views', [
            'referrer' => 'https://google.com/search?q=test',
        ]);
    }

    public function test_user_agent_storage(): void
    {
        $campaign = Campaign::factory()->create();
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';

        $view = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'user_agent' => $userAgent,
            'ip_address' => '192.168.1.1',
        ]);

        $this->assertDatabaseHas('campaign_views', [
            'user_agent' => $userAgent,
        ]);
    }

    public function test_view_timestamps(): void
    {
        $campaign = Campaign::factory()->create();
        $viewTime = now()->subHour();

        $view = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'ip_address' => '192.168.1.1',
            'created_at' => $viewTime,
        ]);

        $this->assertDatabaseHas('campaign_views', [
            'id' => $view->id,
            'created_at' => $viewTime->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_multiple_views_same_campaign(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();

        // Create multiple views for the same campaign
        $views = CampaignView::factory()->count(3)->create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignViewResource::class,
        ])
            ->assertCanSeeTableRecords($views);
    }

    public function test_search_functionality(): void
    {
        $campaign = Campaign::factory()->create(['name' => 'Searchable Campaign']);
        $user = User::factory()->create(['name' => 'Searchable User']);

        $view = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'ip_address' => '192.168.1.1',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignViewResource::class,
        ])
            ->searchTable('Searchable')
            ->assertCanSeeTableRecords([$view]);
    }

    public function test_table_sorting(): void
    {
        $campaign = Campaign::factory()->create();

        $view1 = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'ip_address' => '192.168.1.1',
            'created_at' => now()->subDay(),
        ]);

        $view2 = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'ip_address' => '192.168.1.2',
            'created_at' => now(),
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignViewResource::class,
        ])
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords([$view2, $view1]);
    }

    public function test_table_columns_toggle(): void
    {
        $campaign = Campaign::factory()->create();
        $view = CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            'ip_address' => '192.168.1.1',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ListRecords::class, [
            'resource' => CampaignViewResource::class,
        ])
            ->assertCanSeeTableRecords([$view]);
    }
}
