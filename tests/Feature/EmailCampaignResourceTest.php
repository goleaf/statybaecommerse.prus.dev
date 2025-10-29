<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\NavigationGroup;
use App\Filament\Resources\EmailCampaignResource;
use App\Models\EmailCampaign;
use App\Models\User;

use function assert;

use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use ReflectionClass;
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
        $campaigns = EmailCampaign::factory()
            ->count(3)
            // Force the campaigns to be active so the global ActiveScope keeps them visible in assertions.
            ->state(['is_active' => true])
            ->create();

        Livewire::test(\App\Filament\Resources\EmailCampaignResource\Pages\ListEmailCampaigns::class)
            ->assertCanSeeTableRecords($campaigns);
    }

    public function test_can_create_email_campaign(): void
    {
        // Provide the minimal payload the form expects, including a basic plain-text body.
        $campaignData = [
            'name'         => 'Test Campaign',
            'description'  => 'Test campaign description',
            'subject'      => 'Test Subject',
            'content'      => 'Test campaign body',
            'from_email'   => 'test@example.com',
            'from_name'    => 'Test Sender',
            'reply_to'     => 'reply@example.com',
            'scheduled_at' => now()->addDay(),
            'is_active'    => true,
        ];

        Livewire::test(\App\Filament\Resources\EmailCampaignResource\Pages\CreateEmailCampaign::class)
            ->fillForm($campaignData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('email_campaigns', [
            'name'       => 'Test Campaign',
            'subject'    => 'Test Subject',
            'from_email' => 'test@example.com',
        ]);
    }

    public function test_can_edit_email_campaign(): void
    {
        $campaign = EmailCampaign::factory()->create(['is_active' => true]);

        Livewire::test(\App\Filament\Resources\EmailCampaignResource\Pages\EditEmailCampaign::class, [
            'record' => $campaign->id,
        ])
            ->fillForm([
                'name'    => 'Updated Campaign Name',
                'subject' => 'Updated Subject',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('email_campaigns', [
            'id'      => $campaign->id,
            'name'    => 'Updated Campaign Name',
            'subject' => 'Updated Subject',
        ]);
    }

    public function test_can_view_email_campaign(): void
    {
        $campaign = EmailCampaign::factory()->create(['is_active' => true]);

        Livewire::test(\App\Filament\Resources\EmailCampaignResource\Pages\ViewEmailCampaign::class, [
            'record' => $campaign->id,
        ])
            ->assertOk();
    }

    public function test_can_delete_email_campaign(): void
    {
        $campaign = EmailCampaign::factory()->create(['is_active' => true]);

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
                'name'       => '',  // Required field
                'subject'    => '',  // Required field
                'from_email' => 'invalid-email',  // Invalid email
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'subject', 'from_email']);
    }

    public function test_campaign_creator_relationship(): void
    {
        $user = User::factory()->create();
        /** @var EmailCampaign $campaign */
        $campaign = EmailCampaign::factory()->create(['created_by' => $user->id]);

        $this->assertNotNull($campaign->creator);
        $this->assertInstanceOf(User::class, $campaign->creator);
        $this->assertEquals($user->id, $campaign->creator->id);
    }

    public function test_campaign_template_relationship(): void
    {
        $template = \App\Models\NotificationTemplate::factory()->create();
        /** @var EmailCampaign $campaign */
        $campaign = EmailCampaign::factory()->create(['template_id' => $template->id]);

        $this->assertNotNull($campaign->template);
        $this->assertInstanceOf(\App\Models\NotificationTemplate::class, $campaign->template);
        $this->assertEquals($template->id, $campaign->template->id);
    }

    public function test_campaign_recipients_relationship(): void
    {
        /** @var EmailCampaign $campaign */
        $campaign = EmailCampaign::factory()->create();
        $recipient = \App\Models\EmailCampaignRecipient::factory()->create(['email_campaign_id' => $campaign->id]);

        $this->assertTrue($campaign->recipients()->exists());
        $firstRecipient = $campaign->recipients()->first();
        $this->assertNotNull($firstRecipient);
        assert($firstRecipient instanceof \App\Models\EmailCampaignRecipient);
        $this->assertEquals($recipient->id, $firstRecipient->id);
    }

    public function test_campaign_scope_active(): void
    {
        /** @var EmailCampaign $activeCampaign */
        $activeCampaign = EmailCampaign::factory()->create(['is_active' => true]);
        EmailCampaign::factory()->create(['is_active' => false]);

        $activeCampaigns = EmailCampaign::active()->get();
        $this->assertCount(1, $activeCampaigns);
        $firstActive = $activeCampaigns->first();
        $this->assertNotNull($firstActive);
        $this->assertInstanceOf(EmailCampaign::class, $firstActive);
        $this->assertEquals($activeCampaign->id, $firstActive->id);
    }

    public function test_campaign_scope_scheduled(): void
    {
        /** @var EmailCampaign $scheduledCampaign */
        $scheduledCampaign = EmailCampaign::factory()->create([
            'status'    => 'scheduled',
            'is_active' => true,
        ]);
        EmailCampaign::factory()->create([
            'status'    => 'sent',
            'is_active' => true,
        ]);

        $scheduledCampaigns = EmailCampaign::scheduled()->get();
        $this->assertCount(1, $scheduledCampaigns);
        $firstScheduled = $scheduledCampaigns->first();
        $this->assertNotNull($firstScheduled);
        $this->assertInstanceOf(EmailCampaign::class, $firstScheduled);
        $this->assertEquals($scheduledCampaign->id, $firstScheduled->id);
    }

    public function test_campaign_scope_sent(): void
    {
        /** @var EmailCampaign $sentCampaign */
        $sentCampaign = EmailCampaign::factory()->create([
            'status'    => 'sent',
            'is_active' => true,
        ]);
        EmailCampaign::factory()->create([
            'status'    => 'scheduled',
            'is_active' => true,
        ]);

        $sentCampaigns = EmailCampaign::sent()->get();
        $this->assertCount(1, $sentCampaigns);
        $firstSent = $sentCampaigns->first();
        $this->assertNotNull($firstSent);
        $this->assertInstanceOf(EmailCampaign::class, $firstSent);
        $this->assertEquals($sentCampaign->id, $firstSent->id);
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
            'is_active'    => true,
            'status'       => 'scheduled',
            'scheduled_at' => now()->subHour(),
        ]);

        $inactiveCampaign = EmailCampaign::factory()->create([
            'is_active'    => false,
            'status'       => 'scheduled',
            'scheduled_at' => now()->subHour(),
        ]);

        $futureCampaign = EmailCampaign::factory()->create([
            'is_active'    => true,
            'status'       => 'scheduled',
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
            'sent_at'      => '2024-01-01 11:00:00',
            'is_active'    => '1',
            'settings'     => ['key' => 'value'],
            'metadata'     => ['meta' => 'data'],
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $campaign->scheduled_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $campaign->sent_at);
        $this->assertTrue($campaign->is_active);
        $this->assertIsArray($campaign->settings);
        $this->assertIsArray($campaign->metadata);
    }

    public function test_campaign_fillable_attributes(): void
    {
        // Seed concrete relations so foreign key constraints remain satisfied during the assertion loop.
        $template = \App\Models\NotificationTemplate::factory()->create();
        $user = User::factory()->create();

        $data = [
            'name'         => 'Test Campaign',
            'description'  => 'Test description',
            'subject'      => 'Test Subject',
            'content'      => 'Test content',
            'from_email'   => 'test@example.com',
            'from_name'    => 'Test Sender',
            'reply_to'     => 'reply@example.com',
            'scheduled_at' => now(),
            'sent_at'      => now(),
            'is_active'    => true,
            'status'       => 'scheduled',
            'template_id'  => $template->id,
            'created_by'   => $user->id,
            'settings'     => ['key' => 'value'],
            'metadata'     => ['meta' => 'data'],
        ];

        $campaign = EmailCampaign::create($data);

        foreach ($data as $key => $value) {
            if ($value instanceof DateTimeInterface) {
                $campaignValue = $campaign->$key;

                $this->assertInstanceOf(DateTimeInterface::class, $campaignValue);
                $this->assertSame($value->format('Y-m-d H:i:s'), $campaignValue->format('Y-m-d H:i:s'));

                continue;
            }

            $this->assertEquals($value, $campaign->$key);
        }
    }

    public function test_navigation_group_uses_campaigns_enum(): void
    {
        // Ensure the resource stays grouped under the campaigns navigation heading.
        $reflection = new ReflectionClass(EmailCampaignResource::class);
        $property = $reflection->getProperty('navigationGroup');
        $property->setAccessible(true);

        $this->assertSame(
            NavigationGroup::Campaigns,
            $property->getValue(),
        );
    }
}
