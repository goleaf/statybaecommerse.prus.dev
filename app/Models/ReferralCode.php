<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Translatable\HasTranslations;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

final class ReferralCode extends Model
{
    use HasFactory, HasTranslations, LogsActivity;

    protected $fillable = [
        'user_id',
        'code',
        'is_active',
        'expires_at',
        'metadata',
        'title',
        'description',
        'usage_limit',
        'usage_count',
        'reward_amount',
        'reward_type',
        'conditions',
        'campaign_id',
        'source',
        'tags',
    ];

    public array $translatable = [
        'title',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
            'metadata' => 'array',
            'usage_limit' => 'integer',
            'usage_count' => 'integer',
            'reward_amount' => 'decimal:2',
            'conditions' => 'array',
            'tags' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['user_id', 'code', 'is_active', 'expires_at', 'title', 'description', 'usage_limit', 'usage_count', 'reward_amount', 'reward_type'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the user this code belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get referrals made with this code
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referral_code', 'code');
    }

    /**
     * Get rewards generated from this code
     */
    public function rewards(): HasMany
    {
        return $this->hasMany(ReferralReward::class, 'referral_code', 'code');
    }

    /**
     * Get campaign this code belongs to
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(ReferralCampaign::class, 'campaign_id');
    }

    /**
     * Get usage logs for this code
     */
    public function usageLogs(): HasMany
    {
        return $this->hasMany(ReferralCodeUsageLog::class);
    }

    /**
     * Get statistics for this code
     */
    public function statistics(): HasMany
    {
        return $this->hasMany(ReferralCodeStatistics::class);
    }

    /**
     * Scope for active codes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope for expired codes
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('is_active', false)
            ->orWhere(function ($q) {
                $q->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now());
            });
    }

    /**
     * Scope for codes with usage limit
     */
    public function scopeWithUsageLimit(Builder $query): Builder
    {
        return $query->whereNotNull('usage_limit');
    }

    /**
     * Scope for codes by campaign
     */
    public function scopeByCampaign(Builder $query, int $campaignId): Builder
    {
        return $query->where('campaign_id', $campaignId);
    }

    /**
     * Scope for codes by source
     */
    public function scopeBySource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    /**
     * Scope for codes with tags
     */
    public function scopeWithTags(Builder $query, array $tags): Builder
    {
        return $query->whereJsonContains('tags', $tags);
    }

    /**
     * Scope for codes by reward type
     */
    public function scopeByRewardType(Builder $query, string $rewardType): Builder
    {
        return $query->where('reward_type', $rewardType);
    }

    /**
     * Check if code is still valid
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
     * Check if code has reached usage limit
     */
    public function hasReachedUsageLimit(): bool
    {
        return $this->usage_limit && $this->usage_count >= $this->usage_limit;
    }

    /**
     * Get remaining usage count
     */
    public function getRemainingUsageAttribute(): ?int
    {
        if (!$this->usage_limit) {
            return null;
        }

        return max(0, $this->usage_limit - $this->usage_count);
    }

    /**
     * Get usage percentage
     */
    public function getUsagePercentageAttribute(): ?float
    {
        if (!$this->usage_limit) {
            return null;
        }

        return ($this->usage_count / $this->usage_limit) * 100;
    }

    /**
     * Deactivate the code
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Find code by string
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }

    /**
     * Generate a unique referral code
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /**
     * Get the referral URL for this code
     */
    public function getReferralUrlAttribute(): string
    {
        return url('/register?ref=' . $this->code);
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
        
        // Log the usage
        $this->usageLogs()->create([
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->header('referer'),
        ]);
    }

    /**
     * Get localized title
     */
    public function getLocalizedTitleAttribute(): string
    {
        return $this->getTranslation('title', app()->getLocale()) ?: $this->title;
    }

    /**
     * Get localized description
     */
    public function getLocalizedDescriptionAttribute(): string
    {
        return $this->getTranslation('description', app()->getLocale()) ?: $this->description;
    }

    /**
     * Get formatted reward amount
     */
    public function getFormattedRewardAmountAttribute(): ?string
    {
        if (!$this->reward_amount) {
            return null;
        }

        return number_format($this->reward_amount, 2) . ' EUR';
    }

    /**
     * Check if code meets conditions
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
     * Evaluate a single condition
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
     * Get display data for frontend
     */
    public function getDisplayDataAttribute(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->localized_title,
            'description' => $this->localized_description,
            'is_active' => $this->is_active,
            'is_valid' => $this->isValid(),
            'expires_at' => $this->expires_at?->format('Y-m-d H:i:s'),
            'usage_count' => $this->usage_count,
            'usage_limit' => $this->usage_limit,
            'remaining_usage' => $this->remaining_usage,
            'usage_percentage' => $this->usage_percentage,
            'reward_amount' => $this->reward_amount,
            'reward_type' => $this->reward_type,
            'formatted_reward_amount' => $this->formatted_reward_amount,
            'referral_url' => $this->referral_url,
            'tags' => $this->tags,
            'source' => $this->source,
            'campaign_id' => $this->campaign_id,
        ];
    }

    /**
     * Get statistics for this code
     */
    public function getStatsAttribute(): array
    {
        return [
            'total_referrals' => $this->referrals()->count(),
            'completed_referrals' => $this->referrals()->completed()->count(),
            'pending_referrals' => $this->referrals()->active()->count(),
            'total_rewards' => $this->rewards()->count(),
            'total_reward_amount' => $this->rewards()->sum('amount'),
            'usage_count' => $this->usage_count,
            'usage_limit' => $this->usage_limit,
            'usage_percentage' => $this->usage_percentage,
        ];
    }
}

