<?php

declare(strict_types=1);

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function createEmailCampaign(): EmailCampaign
{
    // Create a minimal email campaign record that satisfies fillable attributes for testing.
    return EmailCampaign::query()->create([
        'name'         => 'Unit Test Campaign',
        'description'  => 'Campaign used for EmailCampaignRecipient model tests.',
        'subject'      => 'Unit Test Subject',
        'content'      => 'Unit test content body.',
        'from_email'   => 'sender@example.test',
        'from_name'    => 'Sender Example',
        'reply_to'     => 'reply@example.test',
        'scheduled_at' => now(),
        'sent_at'      => null,
        'is_active'    => true,
        'status'       => 'scheduled',
        'template_id'  => null,
        'created_by'   => null,
        'settings'     => ['track_opens' => true],
        'metadata'     => ['source' => 'unit-test'],
    ]);
}

it('exposes expected fillable attributes', function (): void {
    // Arrange: instantiate the model so we can read its fillable configuration.
    $model = new EmailCampaignRecipient;

    // Act: capture the fillable properties declared on the model.
    $fillable = $model->getFillable();

    // Assert: ensure we keep the mass-assignable contract in sync.
    expect($fillable)->toBe([
        'email_campaign_id',
        'email',
        'name',
        'status',
        'metadata',
        'meta',
        'scheduled_at',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'bounced_at',
        'unsubscribed_at',
        'bounce_reason',
        'error_message',
        'open_count',
        'click_count',
        'delivery_attempts',
        'is_delivered',
        'is_opened',
        'is_clicked',
        'is_bounced',
        'is_unsubscribed',
    ]);
});

it('defines the expected attribute casts', function (): void {
    // Arrange: use a new instance because getCasts() inspects the metadata from the class definition.
    $model = new EmailCampaignRecipient;

    // Act: fetch the cast definitions applied to model attributes.
    $casts = $model->getCasts();

    // Assert: validate we keep serialization and mutation behaviour untouched.
    expect($casts)->toMatchArray([
        'metadata'          => 'array',
        'meta'              => 'array',
        'scheduled_at'      => 'datetime',
        'sent_at'           => 'datetime',
        'delivered_at'      => 'datetime',
        'opened_at'         => 'datetime',
        'clicked_at'        => 'datetime',
        'bounced_at'        => 'datetime',
        'unsubscribed_at'   => 'datetime',
        'open_count'        => 'integer',
        'click_count'       => 'integer',
        'delivery_attempts' => 'integer',
        'is_delivered'      => 'boolean',
        'is_opened'         => 'boolean',
        'is_clicked'        => 'boolean',
        'is_bounced'        => 'boolean',
        'is_unsubscribed'   => 'boolean',
    ]);
});

it('uses sensible default attribute values', function (): void {
    // Arrange: instantiate the model without persisting it to inspect default attributes.
    $model = new EmailCampaignRecipient;

    // Assert: ensure defaults preserve pending state counters.
    expect($model->status)->toBe('pending');
    expect($model->open_count)->toBe(0);
    expect($model->click_count)->toBe(0);
    expect($model->delivery_attempts)->toBe(0);
    expect($model->is_delivered)->toBeFalse();
    expect($model->is_opened)->toBeFalse();
    expect($model->is_clicked)->toBeFalse();
    expect($model->is_bounced)->toBeFalse();
    expect($model->is_unsubscribed)->toBeFalse();
});

it('belongs to an email campaign', function (): void {
    // Arrange: build a campaign with a recipient linked to it.
    $campaign = createEmailCampaign();
    $recipient = EmailCampaignRecipient::factory()->create([
        'email_campaign_id' => $campaign->id,
    ]);

    // Assert: confirm the inverse relationship exposes the owning campaign.
    expect($recipient->campaign)->toBeInstanceOf(EmailCampaign::class);
    expect($recipient->campaign?->id)->toBe($campaign->id);
});

it('orders recipients alphabetically through the orderedByName scope', function (): void {
    // Arrange: create deterministic recipients so the alphabetical order is predictable.
    $campaign = createEmailCampaign();
    EmailCampaignRecipient::factory()->create([
        'email_campaign_id' => $campaign->id,
        'name'              => 'Charlie Recipient',
    ]);
    EmailCampaignRecipient::factory()->create([
        'email_campaign_id' => $campaign->id,
        'name'              => 'Alice Recipient',
    ]);
    EmailCampaignRecipient::factory()->create([
        'email_campaign_id' => $campaign->id,
        'name'              => 'Beatrice Recipient',
    ]);

    // Act: collect the ordered names via the custom scope.
    $orderedNames = EmailCampaignRecipient::query()
        ->orderedByName()
        ->pluck('name')
        ->all();

    // Assert: the names should come back in alphabetical order (A → Z).
    expect($orderedNames)->toBe([
        'Alice Recipient',
        'Beatrice Recipient',
        'Charlie Recipient',
    ]);
});
