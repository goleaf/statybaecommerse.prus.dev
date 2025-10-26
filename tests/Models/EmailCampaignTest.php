<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class EmailCampaignTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_configuration_matches_expected_columns(): void
    {
        // Arrange: instantiate the model to inspect its fillable metadata.
        $campaign = new EmailCampaign;

        // Act: grab the fillable fields defined on the model.
        $fillable = $campaign->getFillable();

        // Assert: verify every migration-backed column is available for mass-assignment.
        $this->assertSame([
            'name',
            'description',
            'subject',
            'content',
            'html_content',
            'from_email',
            'from_name',
            'reply_to',
            'scheduled_at',
            'sent_at',
            'completed_at',
            'is_active',
            'status',
            'template_id',
            'created_by',
            'settings',
            'metadata',
            'meta',
            'target_audience',
            'total_recipients',
            'sent_count',
            'delivered_count',
            'opened_count',
            'clicked_count',
            'unsubscribed_count',
        ], $fillable);
    }

    public function test_casts_configuration_covers_temporal_and_json_columns(): void
    {
        // Arrange: instantiate the model to inspect cast definitions.
        $campaign = new EmailCampaign;

        // Act: retrieve configured casts from the model.
        $casts = $campaign->getCasts();

        // Assert: ensure important numeric, date, and JSON attributes are typed correctly.
        $this->assertSame('datetime', $casts['scheduled_at']);
        $this->assertSame('datetime', $casts['sent_at']);
        $this->assertSame('datetime', $casts['completed_at']);
        $this->assertSame('boolean', $casts['is_active']);
        $this->assertSame('integer', $casts['total_recipients']);
        $this->assertSame('integer', $casts['sent_count']);
        $this->assertSame('integer', $casts['delivered_count']);
        $this->assertSame('integer', $casts['opened_count']);
        $this->assertSame('integer', $casts['clicked_count']);
        $this->assertSame('integer', $casts['unsubscribed_count']);
        $this->assertSame('array', $casts['target_audience']);
        $this->assertSame('array', $casts['settings']);
        $this->assertSame('array', $casts['metadata']);
        $this->assertSame('array', $casts['meta']);
    }

    public function test_scopes_filter_records_by_activity_and_status(): void
    {
        // Arrange: prepare campaigns for each relevant scenario.
        $activeScheduled = EmailCampaign::factory()->scheduled()->create([
            'is_active'    => true,
            'scheduled_at' => Carbon::now()->addHour(),
        ]);

        $inactiveScheduled = EmailCampaign::factory()->scheduled()->create([
            'is_active'    => false,
            'scheduled_at' => Carbon::now()->addHours(2),
        ]);

        $sentCampaign = EmailCampaign::factory()->sent()->create([
            'is_active' => true,
        ]);

        $draftCampaign = EmailCampaign::factory()->draft()->create([
            'is_active' => true,
        ]);

        // Act: execute each scope while bypassing the global ActiveScope to inspect all records.
        $activeCampaigns = EmailCampaign::withoutGlobalScopes()->active()->pluck('id');
        $scheduledCampaigns = EmailCampaign::withoutGlobalScopes()->scheduled()->pluck('id');
        $sentCampaigns = EmailCampaign::withoutGlobalScopes()->sent()->pluck('id');

        // Assert: confirm only the appropriate identifiers appear for each scope result.
        $this->assertContains($activeScheduled->id, $activeCampaigns);
        $this->assertNotContains($inactiveScheduled->id, $activeCampaigns);

        $this->assertContains($activeScheduled->id, $scheduledCampaigns);
        $this->assertContains($inactiveScheduled->id, $scheduledCampaigns);
        $this->assertNotContains($draftCampaign->id, $scheduledCampaigns);

        $this->assertContains($sentCampaign->id, $sentCampaigns);
        $this->assertNotContains($activeScheduled->id, $sentCampaigns);
    }

    public function test_scope_ordered_by_name_sorts_records(): void
    {
        // Arrange: create two campaigns with deterministic names.
        $alpha = EmailCampaign::factory()->create(['name' => 'Alpha Campaign']);
        $bravo = EmailCampaign::factory()->create(['name' => 'Bravo Campaign']);

        // Act: retrieve the campaigns using both ascending and descending sort orders.
        $ascending = EmailCampaign::withoutGlobalScopes()->orderedByName()->pluck('name')->all();
        $descending = EmailCampaign::withoutGlobalScopes()->orderedByName('desc')->pluck('name')->all();

        // Assert: verify the ordering logic works in both directions.
        $this->assertSame(['Alpha Campaign', 'Bravo Campaign'], $ascending);
        $this->assertSame(['Bravo Campaign', 'Alpha Campaign'], $descending);
    }

    public function test_scope_with_status_accepts_single_and_multiple_values(): void
    {
        // Arrange: create campaigns with three distinct statuses to exercise filtering.
        $scheduled = EmailCampaign::factory()->scheduled()->create();
        $sent = EmailCampaign::factory()->sent()->create();
        $paused = EmailCampaign::factory()->create(['status' => EmailCampaign::STATUS_PAUSED]);

        // Act: query using both scalar and array inputs.
        $scheduledOnly = EmailCampaign::withoutGlobalScopes()->withStatus(EmailCampaign::STATUS_SCHEDULED)->pluck('id');
        $sentAndPaused = EmailCampaign::withoutGlobalScopes()->withStatus([
            EmailCampaign::STATUS_SENT,
            EmailCampaign::STATUS_PAUSED,
        ])->pluck('id');

        // Assert: ensure the scope honours both forms of input.
        $this->assertSame([$scheduled->id], $scheduledOnly->all());
        $this->assertSameCanonicalizing([$sent->id, $paused->id], $sentAndPaused->all());
    }

    public function test_relationships_return_expected_models(): void
    {
        // Arrange: hydrate related models using dedicated factories for clarity.
        $user = User::factory()->create();
        $template = NotificationTemplate::factory()->create();
        $campaign = EmailCampaign::factory()
            ->withCreator($user)
            ->withTemplate($template)
            ->create();

        EmailCampaignRecipient::factory()->count(2)->create([
            'email_campaign_id' => $campaign->id,
        ]);

        // Assert: confirm each relationship resolves correctly.
        $this->assertTrue($campaign->creator->is($user));
        $this->assertTrue($campaign->template->is($template));
        $this->assertCount(2, $campaign->recipients);
    }

    public function test_status_helpers_reflect_current_state(): void
    {
        // Arrange: persist campaigns that cover scheduled and sent paths.
        $scheduled = EmailCampaign::factory()->scheduled()->create([
            'scheduled_at' => Carbon::now()->subHour(),
        ]);
        $sent = EmailCampaign::factory()->sent()->create();

        // Assert: validate helper flags mirror the status column.
        $this->assertTrue($scheduled->fresh()->isScheduled());
        $this->assertFalse($scheduled->fresh()->isSent());

        $this->assertTrue($sent->fresh()->isSent());
        $this->assertFalse($sent->fresh()->isScheduled());
    }

    public function test_can_be_sent_applies_all_guards(): void
    {
        // Arrange: craft variations covering active, inactive, and future scheduling cases.
        $sendable = EmailCampaign::factory()->scheduled()->create([
            'is_active'    => true,
            'scheduled_at' => Carbon::now()->subMinute(),
        ]);

        $inactive = EmailCampaign::factory()->scheduled()->create([
            'is_active'    => false,
            'scheduled_at' => Carbon::now()->subMinute(),
        ]);

        $future = EmailCampaign::factory()->scheduled()->create([
            'is_active'    => true,
            'scheduled_at' => Carbon::now()->addMinute(),
        ]);

        // Assert: only the past-due, active campaign should be sendable.
        $this->assertTrue($sendable->fresh()->canBeSent());
        $this->assertFalse($inactive->fresh()->canBeSent());
        $this->assertFalse($future->fresh()->canBeSent());
    }

    public function test_status_constant_collection_is_comprehensive(): void
    {
        // Assert: confirm the status inventory matches the documented states.
        $this->assertSame([
            EmailCampaign::STATUS_DRAFT,
            EmailCampaign::STATUS_SCHEDULED,
            EmailCampaign::STATUS_SENDING,
            EmailCampaign::STATUS_SENT,
            EmailCampaign::STATUS_PAUSED,
            EmailCampaign::STATUS_CANCELLED,
        ], EmailCampaign::STATUSES);
    }
}
