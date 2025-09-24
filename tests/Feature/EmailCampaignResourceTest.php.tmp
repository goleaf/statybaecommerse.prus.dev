<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\TestCase;

class EmailCampaignResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
        ]));
    }

    public function test_can_list_email_campaigns(): void
    {
        $campaigns = EmailCampaign::factory()->count(3)->create();

        Livewire::test(\App\Filament\Resources\EmailCampaignResource\Pages\ListEmailCampaigns::class)
            ->assertCanSeeTableRecords($campaigns);
    }

    public function test_can_create_email_campaign(): void
    {
        $campaignData = [
            'name' => 'Test Campaign',
            'description' => 'Test campaign description',
            'subject' => 'Test Subject',
            'from_email' => 'test@example.com',
            'from_name' => 'Test Sender',
            'reply_to' => 'reply@example.com',
            'scheduled_at' => now()->addDay(),
            'is_active' => true,
        ];

        Livewire::test(\App\Filament\Resources\EmailCampaignResource\Pages\CreateEmailCampaign::class)
            ->fillForm($campaignData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('email_campaigns', [
            'name' => 'Test Campaign',
            'subject' => 'Test Subject',
            'from_email' => 'test@example.com',
        ]);
    }

    public function test_can_edit_email_campaign(): void
    {
        $campaign = EmailCampaign::factory()->create();

        Livewire::test(\App\Filament\Resources\EmailCampaignResource\Pages\EditEmailCampaign::class, [
            'record' => $campaign->id,
        ])
            ->fillForm([
                'name' => 'Updated Campaign Name',
                'subject' => 'Updated Subject',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('email_campaigns', [
            'id' => $campaign->id,
            'name' => 'Updated Campaign Name',
            'subject' => 'Updated Subject',
        ]);
    }

    public function test_can_view_email_campaign(): void
    {
        $campaign = EmailCampaign::factory()->create();

        Livewire::test(\App\Filament\Resources\EmailCampaignResource\Pages\ViewEmailCampaign::class, [
            'record' => $campaign->id,
        ])
            ->assertOk();
    }

    public function test_can_delete_email_campaign(): void
    {
        $campaign = EmailCampaign::factory()->create();

        Livewire::test(\App\Filament\Resources\EmailCampaignResource\Pages\ListEmailCampaigns::class)
            ->callTableAction('delete', $campaign)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('email_campaigns', [
            'id' => $campaign->id,
        ]);
    }

    public function test_can_filter_campaigns_by_active_status(): void
    {
        $activeCampaign = EmailCampaign::factory()->create(['is_active' => true]);
        $inactiveCampaign = EmailCampaign::factory()->create(['is_active' => false]);

        Livewire::test(\App\Filament\Resources\EmailCampaignResource\Pages\ListEmailCampaigns::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$activeCampaign])
            ->assertCanNotSeeTableRecords([$inactiveCampaign]);
    }

    public function test_campaign_form_validation(): void
    {
        Livewire::test(\App\Filament\Resources\EmailCampaignResource\Pages\CreateEmailCampaign::class)
            ->fillForm([
                'name' => '', // Required field
                'subject' => '', // Required field
                'from_email' => 'invalid-email', // Invalid email
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'subject', 'from_email']);
    }

    public function test_campaign_creator_relationship(): void
    {
        $user = User::factory()->create();
        $campaign = EmailCampaign::factory()->create(['created_by' => $user->id]);

        $this->assertEquals($user->id, $campaign->creator->id);
    }

    public function test_campaign_template_relationship(): void
    {
        $template = \App\Models\NotificationTemplate::factory()->create();
        $campaign = EmailCampaign::factory()->create(['template_id' => $template->id]);

        $this->assertEquals($template->id, $campaign->template->id);
    }

    public function test_campaign_recipients_relationship(): void
    {
        $campaign = EmailCampaign::factory()->create();
        $recipient = \App\Models\EmailCampaignRecipient::factory()->create(['email_campaign_id' => $campaign->id]);

        $this->assertTrue($campaign->recipients()->exists());
        $this->assertEquals($recipient->id, $campaign->recipients()->first()->id);
    }

    public function test_campaign_scope_active(): void
    {
        $activeCampaign = EmailCampaign::factory()->create(['is_active' => true]);
        $inactiveCampaign = EmailCampaign::factory()->create(['is_active' => false]);

        $activeCampaigns = EmailCampaign::active()->get();
        $this->assertCount(1, $activeCampaigns);
        $this->assertEquals($activeCampaign->id, $activeCampaigns->first()->id);
    }

    public function test_campaign_scope_scheduled(): void
    {
        $scheduledCampaign = EmailCampaign::factory()->create(['status' => 'scheduled']);
        $sentCampaign = EmailCampaign::factory()->create(['status' => 'sent']);

        $scheduledCampaigns = EmailCampaign::scheduled()->get();
        $this->assertCount(1, $scheduledCampaigns);
        $this->assertEquals($scheduledCampaign->id, $scheduledCampaigns->first()->id);
    }

    public function test_campaign_scope_sent(): void
    {
        $sentCampaign = EmailCampaign::factory()->create(['status' => 'sent']);
        $scheduledCampaign = EmailCampaign::factory()->create(['status' => 'scheduled']);

        $sentCampaigns = EmailCampaign::sent()->get();
        $this->assertCount(1, $sentCampaigns);
        $this->assertEquals($sentCampaign->id, $sentCampaigns->first()->id);
    }

    public function test_campaign_is_scheduled(): void
    {
        $scheduledCampaign = EmailCampaign::factory()->create(['status' => 'scheduled']);
        $sentCampaign = EmailCampaign::factory()->create(['status' => 'sent']);

        $this->assertTrue($scheduledCampaign->isScheduled());
        $this->assertFalse($sentCampaign->isScheduled());
    }

    public function test_campaign_is_sent(): void
    {
        $sentCampaign = EmailCampaign::factory()->create(['status' => 'sent']);
        $scheduledCampaign = EmailCampaign::factory()->create(['status' => 'scheduled']);

        $this->assertTrue($sentCampaign->isSent());
        $this->assertFalse($scheduledCampaign->isSent());
    }

    public function test_campaign_can_be_sent(): void
    {
        $activeScheduledCampaign = EmailCampaign::factory()->create([
            'is_active' => true,
            'status' => 'scheduled',
            'scheduled_at' => now()->subHour(),
        ]);

        $inactiveCampaign = EmailCampaign::factory()->create([
            'is_active' => false,
            'status' => 'scheduled',
            'scheduled_at' => now()->subHour(),
        ]);

        $futureCampaign = EmailCampaign::factory()->create([
            'is_active' => true,
            'status' => 'scheduled',
            'scheduled_at' => now()->addHour(),
        ]);

        $this->assertTrue($activeScheduledCampaign->canBeSent());
        $this->assertFalse($inactiveCampaign->canBeSent());
        $this->assertFalse($futureCampaign->canBeSent());
    }

    public function test_campaign_casts(): void
    {
        $campaign = EmailCampaign::factory()->create([
            'scheduled_at' => '2024-01-01 10:00:00',
            'sent_at' => '2024-01-01 11:00:00',
            'is_active' => '1',
            'settings' => ['key' => 'value'],
            'metadata' => ['meta' => 'data'],
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $campaign->scheduled_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $campaign->sent_at);
        $this->assertTrue($campaign->is_active);
        $this->assertIsArray($campaign->settings);
        $this->assertIsArray($campaign->metadata);
    }

    public function test_campaign_fillable_attributes(): void
    {
        $data = [
            'name' => 'Test Campaign',
            'description' => 'Test description',
            'subject' => 'Test Subject',
            'content' => 'Test content',
            'from_email' => 'test@example.com',
            'from_name' => 'Test Sender',
            'reply_to' => 'reply@example.com',
            'scheduled_at' => now(),
            'sent_at' => now(),
            'is_active' => true,
            'status' => 'scheduled',
            'template_id' => 1,
            'created_by' => 1,
            'settings' => ['key' => 'value'],
            'metadata' => ['meta' => 'data'],
        ];

        $campaign = EmailCampaign::create($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $campaign->$key);
        }
    }
}
