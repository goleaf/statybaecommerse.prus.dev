<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EmailCampaignRecipient extends Model
{
    /** @use HasFactory<\Database\Factories\EmailCampaignRecipientFactory> */
    use HasFactory;

    use OrdersByName;

    /**
     * Sort recipients by email by default because that column is guaranteed to
     * be unique and present even when no display name is provided.
     */
    protected string $nameColumn = 'email';

    protected $fillable = [
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
    ];

    protected $casts = [
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
    ];

    protected $attributes = [
        'status'            => 'pending',
        'open_count'        => 0,
        'click_count'       => 0,
        'delivery_attempts' => 0,
        'is_delivered'      => false,
        'is_opened'         => false,
        'is_clicked'        => false,
        'is_bounced'        => false,
        'is_unsubscribed'   => false,
    ];

    /**
     * Define the relationship to the owning email campaign instance.
     *
     * @return BelongsTo<EmailCampaign, self>
     */
    public function campaign(): BelongsTo
    {
        /** @var BelongsTo<EmailCampaign, self> $relation */
        $relation = $this->belongsTo(EmailCampaign::class, 'email_campaign_id');

        // Return the cached relationship instance to keep type information precise for static analysis.
        return $relation;
    }

    /**
     * Expose the relationship to the associated customer record when a
     * recipient corresponds to a registered storefront user.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        /** @var BelongsTo<User, self> $relation */
        $relation = $this->belongsTo(User::class);

        return $relation;
    }

    /**
     * Provide access to the marketing subscriber entity so unsubscribes and
     * engagement metrics can be correlated with newsletter signups.
     *
     * @return BelongsTo<Subscriber, self>
     */
    public function subscriber(): BelongsTo
    {
        /** @var BelongsTo<Subscriber, self> $relation */
        $relation = $this->belongsTo(Subscriber::class);

        return $relation;
    }
}
