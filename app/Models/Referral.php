<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\StatusScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Translatable\HasTranslations;

/**
 * Referral
 *
 * Eloquent model representing the Referral entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property array $translatable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Referral newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Referral newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Referral query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, StatusScope::class])]
final class Referral extends Model
{
    use HasFactory;
    use HasTranslations;
    use OrdersByName;

    /**
     * Sort referral listings by their translated title to keep marketing teams
     * aligned with the way campaigns are presented in the UI.
     */
    protected string $nameColumn = 'title';

    protected $fillable = ['referrer_id', 'referred_id', 'referral_code', 'status', 'completed_at', 'expires_at', 'metadata', 'meta', 'source', 'campaign', 'utm_source', 'utm_medium', 'utm_campaign', 'ip_address', 'user_agent', 'title', 'description', 'terms_conditions', 'benefits_description', 'how_it_works', 'seo_title', 'seo_description', 'seo_keywords'];

    public array $translatable = ['title', 'description', 'terms_conditions', 'benefits_description', 'how_it_works'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['completed_at' => 'datetime', 'expires_at' => 'datetime', 'metadata' => 'array', 'meta' => 'array', 'seo_title' => 'array', 'seo_description' => 'array', 'seo_keywords' => 'array'];
    }

    /**
     * Handle referrer functionality with proper error handling.
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Handle referred functionality with proper error handling.
     */
    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    /**
     * Handle rewards functionality with proper error handling.
     */
    public function rewards(): HasMany
    {
        return $this->hasMany(ReferralReward::class);
    }

    /**
     * Resolve the referral code entity linked by the stored referral code string.
     */
    public function codes(): HasMany
    {
        return $this->hasMany(ReferralCode::class, 'code', 'referral_code');
    }

    /**
     * Surface campaign records connected through referral codes.
     */
    public function campaigns(): HasManyThrough
    {
        return $this->hasManyThrough(
            ReferralCampaign::class,
            ReferralCode::class,
            'code',
            'id',
            'referral_code',
            'campaign_id',
        );
    }

    /**
     * Expose aggregated daily statistics related to this referral code.
     */
    public function codeStatistics(): HasManyThrough
    {
        return $this->hasManyThrough(
            ReferralCodeStatistics::class,
            ReferralCode::class,
            'code',
            'referral_code_id',
            'referral_code',
            'id',
        );
    }

    /**
     * Expose usage log records captured for this referral code.
     */
    public function codeUsageLogs(): HasManyThrough
    {
        return $this->hasManyThrough(
            ReferralCodeUsageLog::class,
            ReferralCode::class,
            'code',
            'referral_code_id',
            'referral_code',
            'id',
        );
    }

    /**
     * Expose reward logs connected through referral rewards.
     */
    public function rewardLogs(): HasManyThrough
    {
        return $this->hasManyThrough(
            ReferralRewardLog::class,
            ReferralReward::class,
            'referral_id',
            'referral_reward_id',
            'id',
            'id',
        );
    }

    /**
     * Expose daily statistics for the referral owner.
     */
    public function statistics(): HasMany
    {
        return $this->hasMany(ReferralStatistics::class, 'user_id', 'referrer_id');
    }

    /**
     * Handle referredOrders functionality with proper error handling.
     */
    public function referredOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'referred_id');
    }

    /**
     * Handle translations functionality with proper error handling.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(\App\Models\Translations\ReferralTranslation::class);
    }

    /**
     * Handle latestReward functionality with proper error handling.
     */
    public function latestReward(): HasOne
    {
        return $this->rewards()->one()->latestOfMany();
    }

    /**
     * Handle latestReferredOrder functionality with proper error handling.
     */
    public function latestReferredOrder(): HasOne
    {
        return $this->referredOrders()->one()->latestOfMany();
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'pending')->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Allow filtering by the campaign identifier stored alongside the referral
     * to keep marketing dashboards and exports expressive.
     */
    public function scopeForCampaign(Builder $query, string $campaign): Builder
    {
        return $query->where('campaign', $campaign);
    }

    /**
     * Handle scopeCompleted functionality with proper error handling.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Handle scopeExpired functionality with proper error handling.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired')->orWhere(function ($q) {
            $q->whereNotNull('expires_at')->where('expires_at', '<=', now());
        });
    }

    /**
     * Handle isValid functionality with proper error handling.
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
     * Handle markAsCompleted functionality with proper error handling.
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed', 'completed_at' => now()]);
    }

    /**
     * Handle markAsExpired functionality with proper error handling.
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Handle findByCode functionality with proper error handling.
     */
    public static function findByCode(string $code): ?self
    {
        return self::where('referral_code', $code)->first();
    }

    /**
     * Handle userAlreadyReferred functionality with proper error handling.
     */
    public static function userAlreadyReferred(int $userId): bool
    {
        return self::where('referred_id', $userId)->exists();
    }

    /**
     * Handle canUserRefer functionality with proper error handling.
     */
    public static function canUserRefer(int $userId): bool
    {
        $activeReferrals = self::where('referrer_id', $userId)->active()->count();

        // Default limit of 100 active referrals per user
        return $activeReferrals < 100;
    }

    /**
     * Handle scopeBySource functionality with proper error handling.
     */
    public function scopeBySource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    /**
     * Handle scopeByCampaign functionality with proper error handling.
     */
    public function scopeByCampaign(Builder $query, string $campaign): Builder
    {
        return $query->where('campaign', $campaign);
    }

    /**
     * Handle scopeWithRewards functionality with proper error handling.
     */
    public function scopeWithRewards(Builder $query): Builder
    {
        return $query->has('rewards');
    }

    /**
     * Handle scopeWithoutRewards functionality with proper error handling.
     */
    public function scopeWithoutRewards(Builder $query): Builder
    {
        return $query->doesntHave('rewards');
    }

    /**
     * Handle getTotalRewardsAmountAttribute functionality with proper error handling.
     */
    public function getTotalRewardsAmountAttribute(): float
    {
        return $this->rewards()->sum('amount');
    }

    /**
     * Handle getConversionRateAttribute functionality with proper error handling.
     */
    public function getConversionRateAttribute(): float
    {
        $totalOrders = $this->referredOrders()->count();

        return $totalOrders > 0 ? 100.0 : 0.0;
    }

    /**
     * Handle getDaysSinceCreatedAttribute functionality with proper error handling.
     */
    public function getDaysSinceCreatedAttribute(): int
    {
        return (int) $this->created_at->diffInDays(now());
    }

    /**
     * Handle isAboutToExpire functionality with proper error handling.
     */
    public function isAboutToExpire(): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        return $this->expires_at->isBefore(now()->addDays(7)) && $this->expires_at->isAfter(now());
    }

    /**
     * Handle getPerformanceScoreAttribute functionality with proper error handling.
     */
    public function getPerformanceScoreAttribute(): int
    {
        $score = 0;
        // Base score for completion
        if ($this->status === 'completed') {
            $score += 50;
        }
        // Bonus for having rewards
        if ($this->rewards()->exists()) {
            $score += 20;
        }
        // Bonus for recent completion (only if completed more than 1 day ago)
        if ($this->completed_at && $this->completed_at->isAfter(now()->subDays(30)) && $this->completed_at->isBefore(now()->subDay())) {
            $score += 20;
        }
        // Bonus for orders from referred user
        $orderCount = $this->referredOrders()->count();
        $score += min($orderCount * 5, 10);

        return min($score, 100);
    }

    /**
     * Handle generateUniqueCode functionality with proper error handling.
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Handle createWithCode functionality with proper error handling.
     */
    public static function createWithCode(array $attributes): self
    {
        if (! isset($attributes['referral_code'])) {
            $attributes['referral_code'] = self::generateUniqueCode();
        }

        return self::create($attributes);
    }
}
