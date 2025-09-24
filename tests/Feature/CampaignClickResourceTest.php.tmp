<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Campaign;
use App\Models\CampaignClick;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CampaignClickResourceTest extends TestCase
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
                'clicked_url' => 'https://example.com/test',
                'device_type' => 'desktop',
                'browser' => 'chrome',
                'os' => 'windows',
                'country' => 'Lithuania',
                'utm_source' => 'google',
                'utm_medium' => 'cpc',
                'utm_campaign' => $campaign->name,
                'clicked_at' => now(),
                'is_converted' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('campaign_clicks', [
            'campaign_id' => $campaign->id,
            'customer_id' => $user->id,
            'session_id' => 'test-session-123',
            'ip_address' => '192.168.1.1',
        ]);
    }

    public function test_can_edit_campaign_click(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();
        $click = CampaignClick::factory()->create([
            'campaign_id' => $campaign->id,
            'customer_id' => $user->id,
            'is_converted' => false,
        ]);

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\EditCampaignClick::class, [
            'record' => $click->id,
        ])
            ->fillForm([
                'is_converted' => true,
                'conversion_value' => 99.99,
                'conversion_currency' => 'EUR',
                'notes' => 'Test conversion notes',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $click->refresh();
        $this->assertTrue($click->is_converted);
        $this->assertEquals(99.99, $click->conversion_value);
        $this->assertEquals('EUR', $click->conversion_currency);
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
            ->assertOk();
    }

    public function test_can_filter_campaign_clicks_by_campaign(): void
    {
        $campaign1 = Campaign::factory()->create(['name' => 'Summer Sale']);
        $campaign2 = Campaign::factory()->create(['name' => 'Winter Sale']);
        $user = User::factory()->create();

        CampaignClick::factory()->create([
            'campaign_id' => $campaign1->id,
            'customer_id' => $user->id,
        ]);

        CampaignClick::factory()->create([
            'campaign_id' => $campaign2->id,
            'customer_id' => $user->id,
        ]);

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ListCampaignClicks::class)
            ->filterTable('campaign_id', $campaign1->id)
            ->assertCanSeeTableRecords(CampaignClick::where('campaign_id', $campaign1->id)->get())
            ->assertCanNotSeeTableRecords(CampaignClick::where('campaign_id', $campaign2->id)->get());
    }

    public function test_can_filter_campaign_clicks_by_conversion_status(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();

        $convertedClick = CampaignClick::factory()->create([
            'campaign_id' => $campaign->id,
            'customer_id' => $user->id,
            'is_converted' => true,
        ]);

        $nonConvertedClick = CampaignClick::factory()->create([
            'campaign_id' => $campaign->id,
            'customer_id' => $user->id,
            'is_converted' => false,
        ]);

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ListCampaignClicks::class)
            ->filterTable('is_converted', true)
            ->assertCanSeeTableRecords([$convertedClick])
            ->assertCanNotSeeTableRecords([$nonConvertedClick]);
    }

    public function test_can_mark_conversion_action(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();
        $click = CampaignClick::factory()->create([
            'campaign_id' => $campaign->id,
            'customer_id' => $user->id,
            'is_converted' => false,
        ]);

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ListCampaignClicks::class)
            ->callTableAction('mark_conversion', $click)
            ->assertHasNoTableActionErrors();

        $click->refresh();
        $this->assertTrue($click->is_converted);
    }

    public function test_can_unmark_conversion_action(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();
        $click = CampaignClick::factory()->create([
            'campaign_id' => $campaign->id,
            'customer_id' => $user->id,
            'is_converted' => true,
        ]);

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ListCampaignClicks::class)
            ->callTableAction('unmark_conversion', $click)
            ->assertHasNoTableActionErrors();

        $click->refresh();
        $this->assertFalse($click->is_converted);
    }

    public function test_can_bulk_mark_conversions(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();

        $clicks = CampaignClick::factory()->count(3)->create([
            'campaign_id' => $campaign->id,
            'customer_id' => $user->id,
            'is_converted' => false,
        ]);

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ListCampaignClicks::class)
            ->callTableBulkAction('mark_conversions', $clicks)
            ->assertHasNoTableBulkActionErrors();

        foreach ($clicks as $click) {
            $click->refresh();
            $this->assertTrue($click->is_converted);
        }
    }

    public function test_can_bulk_unmark_conversions(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();

        $clicks = CampaignClick::factory()->count(3)->create([
            'campaign_id' => $campaign->id,
            'customer_id' => $user->id,
            'is_converted' => true,
        ]);

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ListCampaignClicks::class)
            ->callTableBulkAction('unmark_conversions', $clicks)
            ->assertHasNoTableBulkActionErrors();

        foreach ($clicks as $click) {
            $click->refresh();
            $this->assertFalse($click->is_converted);
        }
    }

    public function test_can_delete_campaign_click(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();
        $click = CampaignClick::factory()->create([
            'campaign_id' => $campaign->id,
            'customer_id' => $user->id,
        ]);

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ListCampaignClicks::class)
            ->callTableAction('delete', $click)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('campaign_clicks', [
            'id' => $click->id,
        ]);
    }

    public function test_can_bulk_delete_campaign_clicks(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();

        $clicks = CampaignClick::factory()->count(3)->create([
            'campaign_id' => $campaign->id,
            'customer_id' => $user->id,
        ]);

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\ListCampaignClicks::class)
            ->callTableBulkAction('delete', $clicks)
            ->assertHasNoTableBulkActionErrors();

        foreach ($clicks as $click) {
            $this->assertDatabaseMissing('campaign_clicks', [
                'id' => $click->id,
            ]);
        }
    }

    public function test_form_validation_requires_campaign(): void
    {
        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\CreateCampaignClick::class)
            ->fillForm([
                'campaign_id' => null,
                'customer_id' => User::factory()->create()->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['campaign_id']);
    }

    public function test_form_validation_accepts_valid_ip_address(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\CreateCampaignClick::class)
            ->fillForm([
                'campaign_id' => $campaign->id,
                'customer_id' => $user->id,
                'ip_address' => '192.168.1.1',
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    public function test_form_validation_rejects_invalid_ip_address(): void
    {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();

        Livewire::test(\App\Filament\Resources\CampaignClickResource\Pages\CreateCampaignClick::class)
            ->fillForm([
                'campaign_id' => $campaign->id,
                'customer_id' => $user->id,
                'ip_address' => 'invalid-ip',
            ])
            ->call('create')
            ->assertHasFormErrors(['ip_address']);
    }
}
