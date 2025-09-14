<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\DateRangeScope;
use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;
/**
 * ReferralCode
 * 
 * Eloquent model representing the ReferralCode entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $fillable
 * @property array $translatable
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralCode query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, DateRangeScope::class, UserOwnedScope::class])]
final class ReferralCode extends Model
{
    use HasFactory, HasTranslations, LogsActivity;
    protected $fillable = ['user_id', 'code', 'is_active', 'expires_at', 'metadata', 'title', 'description', 'usage_limit', 'usage_count', 'reward_amount', 'reward_type', 'conditions', 'campaign_id', 'source', 'tags'];
    public array $translatable = ['title', 'description'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'expires_at' => 'datetime', 'metadata' => 'array', 'usage_limit' => 'integer', 'usage_count' => 'integer', 'reward_amount' => 'decimal:2', 'conditions' => 'array', 'tags' => 'array'];
    }
    /**
     * Handle getActivitylogOptions functionality with proper error handling.
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['user_id', 'code', 'is_active', 'expires_at', 'title', 'description', 'usage_limit', 'usage_count', 'reward_amount', 'reward_type'])->logOnlyDirty()->dontSubmitEmptyLogs();
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
     * Handle referrals functionality with proper error handling.
     * @return HasMany
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referral_code', 'code');
    }
    /**
     * Handle rewards functionality with proper error handling.
     * @return HasMany
     */
    public function rewards(): HasMany
    {
        return $this->hasMany(ReferralReward::class, 'referral_code', 'code');
    }
    /**
     * Handle campaign functionality with proper error handling.
     * @return BelongsTo
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(ReferralCampaign::class, 'campaign_id');
    }
    /**
     * Handle usageLogs functionality with proper error handling.
     * @return HasMany
     */
    public function usageLogs(): HasMany
    {
        return $this->hasMany(ReferralCodeUsageLog::class);
    }
    /**
     * Handle statistics functionality with proper error handling.
     * @return HasMany
     */
    public function statistics(): HasMany
    {
        return $this->hasMany(ReferralCodeStatistics::class);
    }
    /**
     * Handle scopeActive functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
    /**
     * Handle scopeExpired functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('is_active', false)->orWhere(function ($q) {
            $q->whereNotNull('expires_at')->where('expires_at', '<=', now());
        });
    }
    /**
     * Handle scopeWithUsageLimit functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithUsageLimit(Builder $query): Builder
    {
        return $query->whereNotNull('usage_limit');
    }
    /**
     * Handle scopeByCampaign functionality with proper error handling.
     * @param Builder $query
     * @param int $campaignId
     * @return Builder
     */
    public function scopeByCampaign(Builder $query, int $campaignId): Builder
    {
        return $query->where('campaign_id', $campaignId);
    }
    /**
     * Handle scopeBySource functionality with proper error handling.
     * @param Builder $query
     * @param string $source
     * @return Builder
     */
    public function scopeBySource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }
    /**
     * Handle scopeWithTags functionality with proper error handling.
     * @param Builder $query
     * @param array $tags
     * @return Builder
     */
    public function scopeWithTags(Builder $query, array $tags): Builder
    {
        return $query->whereJsonContains('tags', $tags);
    }
    /**
     * Handle scopeByRewardType functionality with proper error handling.
     * @param Builder $query
     * @param string $rewardType
     * @return Builder
     */
    public function scopeByRewardType(Builder $query, string $rewardType): Builder
    {
        return $query->where('reward_type', $rewardType);
    }
    /**
     * Handle isValid functionality with proper error handling.
     * @return bool
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }
        return true;
    }
    /**
     * Handle hasReachedUsageLimit functionality with proper error handling.
     * @return bool
     */
    public function hasReachedUsageLimit(): bool
    {
        return $this->usage_limit && $this->usage_count >= $this->usage_limit;
    }
    /**
     * Handle getRemainingUsageAttribute functionality with proper error handling.
     * @return int|null
     */
    public function getRemainingUsageAttribute(): ?int
    {
        if (!$this->usage_limit) {
            return null;
        }
        return max(0, $this->usage_limit - $this->usage_count);
    }
    /**
     * Handle getUsagePercentageAttribute functionality with proper error handling.
     * @return float|null
     */
    public function getUsagePercentageAttribute(): ?float
    {
        if (!$this->usage_limit) {
            return null;
        }
        return $this->usage_count / $this->usage_limit * 100;
    }
    /**
     * Handle deactivate functionality with proper error handling.
     * @return void
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }
    /**
     * Handle findByCode functionality with proper error handling.
     * @param string $code
     * @return self|null
     */
    public static function findByCode(string $code): ?self
    {
        return self::where('code', $code)->first();
    }
    /**
     * Handle generateUniqueCode functionality with proper error handling.
     * @return string
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (self::where('code', $code)->exists());
        return $code;
    }
    /**
     * Handle getReferralUrlAttribute functionality with proper error handling.
     * @return string
     */
    public function getReferralUrlAttribute(): string
    {
        return url('/register?ref=' . $this->code);
    }
    /**
     * Handle incrementUsage functionality with proper error handling.
     * @return void
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
        // Log the usage
        $this->usageLogs()->create(['user_id' => auth()->id(), 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent(), 'referrer' => request()->header('referer')]);
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
     * Handle getFormattedRewardAmountAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getFormattedRewardAmountAttribute(): ?string
    {
        if (!$this->reward_amount) {
            return null;
        }
        return number_format($this->reward_amount, 2) . ' EUR';
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
        return ['id' => $this->id, 'code' => $this->code, 'title' => $this->localized_title, 'description' => $this->localized_description, 'is_active' => $this->is_active, 'is_valid' => $this->isValid(), 'expires_at' => $this->expires_at?->format('Y-m-d H:i:s'), 'usage_count' => $this->usage_count, 'usage_limit' => $this->usage_limit, 'remaining_usage' => $this->remaining_usage, 'usage_percentage' => $this->usage_percentage, 'reward_amount' => $this->reward_amount, 'reward_type' => $this->reward_type, 'formatted_reward_amount' => $this->formatted_reward_amount, 'referral_url' => $this->referral_url, 'tags' => $this->tags, 'source' => $this->source, 'campaign_id' => $this->campaign_id];
    }
    /**
     * Handle getStatsAttribute functionality with proper error handling.
     * @return array
     */
    public function getStatsAttribute(): array
    {
        return ['total_referrals' => $this->referrals()->count(), 'completed_referrals' => $this->referrals()->completed()->count(), 'pending_referrals' => $this->referrals()->active()->count(), 'total_rewards' => $this->rewards()->count(), 'total_reward_amount' => $this->rewards()->sum('amount'), 'usage_count' => $this->usage_count, 'usage_limit' => $this->usage_limit, 'usage_percentage' => $this->usage_percentage];
    }
}