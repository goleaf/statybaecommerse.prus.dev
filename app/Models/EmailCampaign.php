<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    protected $fillable = [
        'name',
        'description',
        'subject',
        'content',
        'from_email',
        'from_name',
        'reply_to',
        'scheduled_at',
        'sent_at',
        'is_active',
        'status',
        'template_id',
        'created_by',
        'settings',
        'metadata',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'is_active' => 'boolean',
        'settings' => 'array',
        'metadata' => 'array',
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
        return $query->where('status', 'scheduled');
    }

    /**
     * Handle scopeSent functionality with proper error handling.
     */
    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', 'sent');
    }

    /**
     * Handle isScheduled functionality with proper error handling.
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    /**
     * Handle isSent functionality with proper error handling.
     */
    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    /**
     * Handle canBeSent functionality with proper error handling.
     */
    public function canBeSent(): bool
    {
        return $this->is_active &&
               $this->status === 'scheduled' &&
               $this->scheduled_at &&
               $this->scheduled_at->isPast();
    }
}
