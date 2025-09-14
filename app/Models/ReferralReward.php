<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

final /**
 * ReferralReward
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class ReferralReward extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'referral_id',
        'user_id',
        'order_id',
        'type',
        'amount',
        'currency_code',
        'status',
        'applied_at',
        'expires_at',
        'metadata',
        'title',
        'description',
        'is_active',
        'priority',
        'conditions',
        'reward_data',
    ];

    public array $translatable = [
        'title',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'applied_at' => 'datetime',
            'expires_at' => 'datetime',
            'metadata' => 'array',
            'conditions' => 'array',
            'reward_data' => 'array',
            'is_active' => 'boolean',
            'priority' => 'integer',
        ];
    }

    /**
     * Get the referral this reward belongs to
     */
    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    /**
     * Get the user this reward belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order this reward was applied to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get reward notifications
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'data->reward_id');
    }

    /**
     * Get reward logs
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ReferralRewardLog::class);
    }

    /**
     * Scope for pending rewards
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for applied rewards
     */
    public function scopeApplied(Builder $query): Builder
    {
        return $query->where('status', 'applied');
    }

    /**
     * Scope for expired rewards
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now());
            });
    }

    /**
     * Scope for referrer bonuses
     */
    public function scopeReferrerBonus(Builder $query): Builder
    {
        return $query->where('type', 'referrer_bonus');
    }

    /**
     * Scope for referred discounts
     */
    public function scopeReferredDiscount(Builder $query): Builder
    {
        return $query->where('type', 'referred_discount');
    }

    /**
     * Scope for active rewards
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for rewards by priority
     */
    public function scopeByPriority(Builder $query, int $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for rewards by user
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for rewards by date range
     */
    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for rewards by amount range
     */
    public function scopeByAmountRange(Builder $query, float $minAmount, float $maxAmount): Builder
    {
        return $query->whereBetween('amount', [$minAmount, $maxAmount]);
    }

    /**
     * Check if reward is still valid
     */
    public function isValid(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Apply the reward
     */
    public function apply(?int $orderId = null): void
    {
        $this->update([
            'status' => 'applied',
            'applied_at' => now(),
            'order_id' => $orderId,
        ]);
    }

    /**
     * Mark reward as expired
     */
    public function markAsExpired(): void
    {
        $this->update([
            'status' => 'expired',
        ]);
    }

    /**
     * Get formatted amount with currency
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2).' '.$this->currency_code;
    }

    /**
     * Check if this is a referrer bonus
     */
    public function isReferrerBonus(): bool
    {
        return $this->type === 'referrer_bonus';
    }

    /**
     * Check if this is a referred discount
     */
    public function isReferredDiscount(): bool
    {
        return $this->type === 'referred_discount';
    }

    /**
     * Get the localized title
     */
    public function getLocalizedTitleAttribute(): string
    {
        return $this->getTranslation('title', app()->getLocale()) ?: $this->title;
    }

    /**
     * Get the localized description
     */
    public function getLocalizedDescriptionAttribute(): string
    {
        return $this->getTranslation('description', app()->getLocale()) ?: $this->description;
    }

    /**
     * Check if reward meets conditions
     */
    public function meetsConditions(array $context = []): bool
    {
        if (empty($this->conditions)) {
            return true;
        }

        foreach ($this->conditions as $condition) {
            if (! $this->evaluateCondition($condition, $context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate a single condition
     */
    private function evaluateCondition(array $condition, array $context): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? '=';
        $value = $condition['value'] ?? null;

        if (! $field || ! isset($context[$field])) {
            return false;
        }

        $contextValue = $context[$field];

        return match ($operator) {
            '=' => $contextValue == $value,
            '!=' => $contextValue != $value,
            '>' => $contextValue > $value,
            '>=' => $contextValue >= $value,
            '<' => $contextValue < $value,
            '<=' => $contextValue <= $value,
            'in' => in_array($contextValue, (array) $value),
            'not_in' => ! in_array($contextValue, (array) $value),
            default => false,
        };
    }

    /**
     * Get reward display data
     */
    public function getDisplayDataAttribute(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->localized_title,
            'description' => $this->localized_description,
            'type' => $this->type,
            'amount' => $this->amount,
            'currency' => $this->currency_code,
            'status' => $this->status,
            'formatted_amount' => $this->formatted_amount,
            'is_valid' => $this->isValid(),
            'expires_at' => $this->expires_at?->format('Y-m-d H:i:s'),
            'applied_at' => $this->applied_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Log reward action
     */
    public function logAction(string $action, array $data = []): void
    {
        $this->logs()->create([
            'action' => $action,
            'data' => $data,
            'user_id' => auth()->id(),
        ]);
    }
}
