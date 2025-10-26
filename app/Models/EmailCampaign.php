<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * EmailCampaign
 *
 * Eloquent model representing the EmailCampaign entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 *
 * @method static \Illuminate\Database\Eloquent\Builder|EmailCampaign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailCampaign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailCampaign query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class EmailCampaign extends Model
{
    use HasFactory;
    use OrdersByName;

    /**
     * Configure the OrdersByName trait to fall back to the campaign name when a
     * dedicated title column is absent, keeping list screens predictable.
     */
    protected string $nameColumn = 'name';

    /**
     * Provide explicit hints for the shared ActiveScope helper so schema
     * introspection is avoided during tests that rely on in-memory SQLite.
     */
    public const SCOPE_COLUMN_HINTS = [
        'is_active' => true,
        'status'    => true,
    ];

    /**
     * Enumerate the supported lifecycle statuses that the factory and
     * application logic expect to work with.
     */
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_SENDING = 'sending';

    public const STATUS_SENT = 'sent';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Collate status values for quick validation and testing.
     */
    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SCHEDULED,
        self::STATUS_SENDING,
        self::STATUS_SENT,
        self::STATUS_PAUSED,
        self::STATUS_CANCELLED,
    ];

    /** @var array<int, string> */
    protected $fillable = [
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
    ];

    /** @var array<string, string> */
    protected $casts = [
        'scheduled_at'       => 'datetime',
        'sent_at'            => 'datetime',
        'completed_at'       => 'datetime',
        'is_active'          => 'boolean',
        'total_recipients'   => 'integer',
        'sent_count'         => 'integer',
        'delivered_count'    => 'integer',
        'opened_count'       => 'integer',
        'clicked_count'      => 'integer',
        'unsubscribed_count' => 'integer',
        'target_audience'    => 'array',
        'settings'           => 'array',
        'metadata'           => 'array',
        'meta'               => 'array',
    ];

    /**
     * Ensure sensible defaults so aggregate helpers stay predictable.
     */
    protected $attributes = [
        'status'             => self::STATUS_DRAFT,
        'is_active'          => true,
        'total_recipients'   => 0,
        'sent_count'         => 0,
        'delivered_count'    => 0,
        'opened_count'       => 0,
        'clicked_count'      => 0,
        'unsubscribed_count' => 0,
    ];

    /**
     * Handle creator functionality with proper error handling.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Handle template functionality with proper error handling.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    /**
     * Handle recipients functionality with proper error handling.
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class);
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Handle scopeScheduled functionality with proper error handling.
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    /**
     * Quickly fetch draft campaigns so editors can manage unpublished content
     * without repeating status checks across controllers.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Handle scopeSent functionality with proper error handling.
     */
    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SENT);
    }

    /**
     * Handle scopeOrderedByName functionality with proper error handling.
     */
    /**
     * Handle scopeWithStatus functionality with proper error handling.
     */
    public function scopeWithStatus(Builder $query, string|array $statuses): Builder
    {
        $statusList = (array) $statuses;

        return $query->whereIn('status', $statusList);
    }

    /**
     * Handle isScheduled functionality with proper error handling.
     */
    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    /**
     * Handle isSent functionality with proper error handling.
     */
    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    /**
     * Handle canBeSent functionality with proper error handling.
     */
    public function canBeSent(): bool
    {
        return $this->is_active &&
               $this->status === self::STATUS_SCHEDULED &&
               $this->scheduled_at instanceof Carbon &&
               $this->scheduled_at->isPast();
    }
}
