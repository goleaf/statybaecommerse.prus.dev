<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\DateRangeScope;
use App\Models\Scopes\StatusScope;
use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
/**
 * ReferralReward
 * 
 * Eloquent model representing the ReferralReward entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $fillable
 * @property array $translatable
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralReward newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralReward newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralReward query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, DateRangeScope::class, StatusScope::class, UserOwnedScope::class])]
final class ReferralReward extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;
    protected $fillable = ['referral_id', 'user_id', 'order_id', 'type', 'amount', 'currency_code', 'status', 'applied_at', 'expires_at', 'metadata', 'title', 'description', 'is_active', 'priority', 'conditions', 'reward_data'];
    public array $translatable = ['title', 'description'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'applied_at' => 'datetime', 'expires_at' => 'datetime', 'metadata' => 'array', 'conditions' => 'array', 'reward_data' => 'array', 'is_active' => 'boolean', 'priority' => 'integer'];
    }
    /**
     * Handle referral functionality with proper error handling.
     * @return BelongsTo
     */
    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }
    /**
     * Handle user functionality with proper error handling.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Handle order functionality with proper error handling.
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    /**
     * Handle notifications functionality with proper error handling.
     * @return HasMany
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'data->reward_id');
    }
    /**
     * Handle logs functionality with proper error handling.
     * @return HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ReferralRewardLog::class);
    }
    /**
     * Handle scopePending functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }
    /**
     * Handle scopeApplied functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeApplied(Builder $query): Builder
    {
        return $query->where('status', 'applied');
    }
    /**
     * Handle scopeExpired functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired')->orWhere(function ($q) {
            $q->whereNotNull('expires_at')->where('expires_at', '<=', now());
        });
    }
    /**
     * Handle scopeReferrerBonus functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeReferrerBonus(Builder $query): Builder
    {
        return $query->where('type', 'referrer_bonus');
    }
    /**
     * Handle scopeReferredDiscount functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeReferredDiscount(Builder $query): Builder
    {
        return $query->where('type', 'referred_discount');
    }
    /**
     * Handle scopeActive functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
    /**
     * Handle scopeByPriority functionality with proper error handling.
     * @param Builder $query
     * @param int $priority
     * @return Builder
     */
    public function scopeByPriority(Builder $query, int $priority): Builder
    {
        return $query->where('priority', $priority);
    }
    /**
     * Handle scopeForUser functionality with proper error handling.
     * @param Builder $query
     * @param int $userId
     * @return Builder
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
    /**
     * Handle scopeByDateRange functionality with proper error handling.
     * @param Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return Builder
     */
    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
    /**
     * Handle scopeByAmountRange functionality with proper error handling.
     * @param Builder $query
     * @param float $minAmount
     * @param float $maxAmount
     * @return Builder
     */
    public function scopeByAmountRange(Builder $query, float $minAmount, float $maxAmount): Builder
    {
        return $query->whereBetween('amount', [$minAmount, $maxAmount]);
    }
    /**
     * Handle isValid functionality with proper error handling.
     * @return bool
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
     * Handle apply functionality with proper error handling.
     * @param int|null $orderId
     * @return void
     */
    public function apply(?int $orderId = null): void
    {
        $this->update(['status' => 'applied', 'applied_at' => now(), 'order_id' => $orderId]);
    }
    /**
     * Handle markAsExpired functionality with proper error handling.
     * @return void
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }
    /**
     * Handle getFormattedAmountAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency_code;
    }
    /**
     * Handle isReferrerBonus functionality with proper error handling.
     * @return bool
     */
    public function isReferrerBonus(): bool
    {
        return $this->type === 'referrer_bonus';
    }
    /**
     * Handle isReferredDiscount functionality with proper error handling.
     * @return bool
     */
    public function isReferredDiscount(): bool
    {
        return $this->type === 'referred_discount';
    }
    /**
     * Handle getLocalizedTitleAttribute functionality with proper error handling.
     * @return string
     */
    public function getLocalizedTitleAttribute(): string
    {
        return $this->getTranslation('title', app()->getLocale()) ?: $this->title;
    }
    /**
     * Handle getLocalizedDescriptionAttribute functionality with proper error handling.
     * @return string
     */
    public function getLocalizedDescriptionAttribute(): string
    {
        return $this->getTranslation('description', app()->getLocale()) ?: $this->description;
    }
    /**
     * Handle meetsConditions functionality with proper error handling.
     * @param array $context
     * @return bool
     */
    public function meetsConditions(array $context = []): bool
    {
        if (empty($this->conditions)) {
            return true;
        }
        foreach ($this->conditions as $condition) {
            if (!$this->evaluateCondition($condition, $context)) {
                return false;
            }
        }
        return true;
    }
    /**
     * Handle evaluateCondition functionality with proper error handling.
     * @param array $condition
     * @param array $context
     * @return bool
     */
    private function evaluateCondition(array $condition, array $context): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? '=';
        $value = $condition['value'] ?? null;
        if (!$field || !isset($context[$field])) {
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
            'not_in' => !in_array($contextValue, (array) $value),
            default => false,
        };
    }
    /**
     * Handle getDisplayDataAttribute functionality with proper error handling.
     * @return array
     */
    public function getDisplayDataAttribute(): array
    {
        return ['id' => $this->id, 'title' => $this->localized_title, 'description' => $this->localized_description, 'type' => $this->type, 'amount' => $this->amount, 'currency' => $this->currency_code, 'status' => $this->status, 'formatted_amount' => $this->formatted_amount, 'is_valid' => $this->isValid(), 'expires_at' => $this->expires_at?->format('Y-m-d H:i:s'), 'applied_at' => $this->applied_at?->format('Y-m-d H:i:s')];
    }
    /**
     * Handle logAction functionality with proper error handling.
     * @param string $action
     * @param array $data
     * @return void
     */
    public function logAction(string $action, array $data = []): void
    {
        $this->logs()->create(['action' => $action, 'data' => $data, 'user_id' => auth()->id()]);
    }
}