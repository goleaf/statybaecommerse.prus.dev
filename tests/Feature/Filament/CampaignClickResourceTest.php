<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\CampaignClick;
use App\Models\Campaign;
use App\Models\User;
use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CampaignClickResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $admin = AdminUser::factory()->create();
        $this->actingAs($admin);
    }

    public function test_can_list_campaign_clicks(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();
        
        $clicks = CampaignClick::factory()->count(3)->create([
            'campaign_id' => $campaign->id,
            'customer_id' => $user->id,
        ]);

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ListCampaignClicks::class)
            ->assertCanSeeTableRecords($clicks);
    }

    public function test_can_create_campaign_click(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\CreateCampaignClick::class)
            ->fillForm([
                'campaign_id' => $campaign->id,
                'customer_id' => $user->id,
                'session_id' => 'test-session-123',
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Mozilla/5.0 Test Browser',
                'click_type' => 'cta',
                'clicked_url' => 'https://example.com/cta',
                'clicked_at' => now(),
                'device_type' => 'desktop',
                'browser' => 'chrome',
                'os' => 'windows',
                'country' => 'Lithuania',
                'city' => 'Vilnius',
                'utm_source' => 'google',
                'utm_medium' => 'cpc',
                'utm_campaign' => 'test-campaign',
                'conversion_value' => 100.50,
                'is_converted' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('campaign_clicks', [
            'campaign_id' => $campaign->id,
            'customer_id' => $user->id,
            'session_id' => 'test-session-123',
            'ip_address' => '192.168.1.1',
            'click_type' => 'cta',
            'is_converted' => true,
        ]);
    }

    public function test_can_edit_campaign_click(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();
        $click = CampaignClick::factory()->create([
            'campaign_id' => $campaign->id,
            'customer_id' => $user->id,
        ]);

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\EditCampaignClick::class, [
            'record' => $click->id,
        ])
            ->fillForm([
                'click_type' => 'banner',
                'is_converted' => true,
                'conversion_value' => 250.75,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $click->refresh();
        $this->assertEquals('banner', $click->click_type);
        $this->assertTrue($click->is_converted);
        $this->assertEquals(250.75, $click->conversion_value);
    }

    public function test_can_view_campaign_click(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();
        $click = CampaignClick::factory()->create([
            'campaign_id' => $campaign->id,
            'customer_id' => $user->id,
        ]);

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ViewCampaignClick::class, [
            'record' => $click->id,
        ])
            ->assertFormSet([
                'campaign_id' => $campaign->id,
                'customer_id' => $user->id,
            ]);
    }

    public function test_can_delete_campaign_click(): void
    {
        $click = CampaignClick::factory()->create();

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ListCampaignClicks::class)
            ->callTableAction('delete', $click);

        $this->assertDatabaseMissing('campaign_clicks', [
            'id' => $click->id,
        ]);
    }

    public function test_can_filter_by_campaign(): void
    {
        $campaign1 = Campaign::factory()->create();
        $campaign2 = Campaign::factory()->create();
        
        $click1 = CampaignClick::factory()->create(['campaign_id' => $campaign1->id]);
        $click2 = CampaignClick::factory()->create(['campaign_id' => $campaign2->id]);

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ListCampaignClicks::class)
            ->filterTable('campaign_id', $campaign1->id)
            ->assertCanSeeTableRecords([$click1])
            ->assertCanNotSeeTableRecords([$click2]);
    }

    public function test_can_filter_by_click_type(): void
    {
        $ctaClick = CampaignClick::factory()->cta()->create();
        $bannerClick = CampaignClick::factory()->banner()->create();

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ListCampaignClicks::class)
            ->filterTable('click_type', 'cta')
            ->assertCanSeeTableRecords([$ctaClick])
            ->assertCanNotSeeTableRecords([$bannerClick]);
    }

    public function test_can_filter_by_device_type(): void
    {
        $mobileClick = CampaignClick::factory()->mobile()->create();
        $desktopClick = CampaignClick::factory()->desktop()->create();

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ListCampaignClicks::class)
            ->filterTable('device_type', 'mobile')
            ->assertCanSeeTableRecords([$mobileClick])
            ->assertCanNotSeeTableRecords([$desktopClick]);
    }

    public function test_can_filter_by_conversion_status(): void
    {
        $convertedClick = CampaignClick::factory()->converted()->create();
        $regularClick = CampaignClick::factory()->create();

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ListCampaignClicks::class)
            ->filterTable('is_converted', '1')
            ->assertCanSeeTableRecords([$convertedClick])
            ->assertCanNotSeeTableRecords([$regularClick]);
    }

    public function test_can_filter_guest_clicks(): void
    {
        $guestClick = CampaignClick::factory()->create(['customer_id' => null]);
        $userClick = CampaignClick::factory()->create(['customer_id' => User::factory()->create()->id]);

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ListCampaignClicks::class)
            ->filterTable('guest_clicks')
            ->assertCanSeeTableRecords([$guestClick])
            ->assertCanNotSeeTableRecords([$userClick]);
    }

    public function test_can_filter_recent_clicks(): void
    {
        $recentClick = CampaignClick::factory()->recent()->create();
        $oldClick = CampaignClick::factory()->create(['clicked_at' => now()->subDays(10)]);

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ListCampaignClicks::class)
            ->filterTable('recent_clicks')
            ->assertCanSeeTableRecords([$recentClick])
            ->assertCanNotSeeTableRecords([$oldClick]);
    }

    public function test_can_export_campaign_clicks(): void
    {
        CampaignClick::factory()->count(5)->create();

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ListCampaignClicks::class)
            ->call('export')
            ->assertFileDownloaded();
    }

    public function test_can_bulk_delete_campaign_clicks(): void
    {
        $clicks = CampaignClick::factory()->count(3)->create();

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ListCampaignClicks::class)
            ->callTableBulkAction('delete', $clicks);

        foreach ($clicks as $click) {
            $this->assertDatabaseMissing('campaign_clicks', [
                'id' => $click->id,
            ]);
        }
    }

    public function test_can_use_tabs(): void
    {
        $convertedClick = CampaignClick::factory()->converted()->create();
        $regularClick = CampaignClick::factory()->create();
        $recentClick = CampaignClick::factory()->recent()->create();
        $ctaClick = CampaignClick::factory()->cta()->create();

        $component = Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ListCampaignClicks::class);

        // Test converted tab
        $component->set('activeTab', 'converted')
            ->assertCanSeeTableRecords([$convertedClick])
            ->assertCanNotSeeTableRecords([$regularClick]);

        // Test recent tab
        $component->set('activeTab', 'recent')
            ->assertCanSeeTableRecords([$recentClick]);

        // Test CTA tab
        $component->set('activeTab', 'cta')
            ->assertCanSeeTableRecords([$ctaClick]);
    }

    public function test_form_validation(): void
    {
        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\CreateCampaignClick::class)
            ->fillForm([
                'campaign_id' => null, // Required field
                'click_type' => 'invalid_type', // Invalid option
                'conversion_value' => 'invalid_number', // Invalid number
            ])
            ->call('create')
            ->assertHasFormErrors([
                'campaign_id',
                'click_type',
                'conversion_value',
            ]);
    }

    public function test_can_search_campaign_clicks(): void
    {
        $campaign = Campaign::factory()->create(['name' => 'Test Campaign']);
        $user = User::factory()->create(['name' => 'Test User']);
        
        $click = CampaignClick::factory()->create([
            'campaign_id' => $campaign->id,
            'customer_id' => $user->id,
        ]);

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ListCampaignClicks::class)
            ->searchTable('Test Campaign')
            ->assertCanSeeTableRecords([$click]);
    }
}
