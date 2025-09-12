<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

final class Referral extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $fillable = [
        'referrer_id',
        'referred_id',
        'referral_code',
        'status',
        'completed_at',
        'expires_at',
        'metadata',
        'source',
        'campaign',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'ip_address',
        'user_agent',
        'title',
        'description',
        'terms_conditions',
        'benefits_description',
        'how_it_works',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    public array $translatable = [
        'title',
        'description',
        'terms_conditions',
        'benefits_description',
        'how_it_works',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
            'metadata' => 'array',
            'seo_keywords' => 'array',
        ];
    }

    /**
     * Get the user who made the referral
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get the user who was referred
     */
    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    /**
     * Get referral rewards
     */
    public function rewards(): HasMany
    {
        return $this->hasMany(ReferralReward::class);
    }

    /**
     * Get referral analytics events
     */
    public function analyticsEvents(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class, 'referral_id');
    }

    /**
     * Get orders from referred user
     */
    public function referredOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'referred_id');
    }

    /**
     * Get referral translations
     */
    public function translations(): HasMany
    {
        return $this->hasMany(\App\Models\Translations\ReferralTranslation::class);
    }

    /**
     * Scope for active referrals
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope for completed referrals
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for expired referrals
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
     * Check if referral is still valid
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
     * Mark referral as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark referral as expired
     */
    public function markAsExpired(): void
    {
        $this->update([
            'status' => 'expired',
        ]);
    }

    /**
     * Get referral by code
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('referral_code', $code)->first();
    }

    /**
     * Check if user has already been referred
     */
    public static function userAlreadyReferred(int $userId): bool
    {
        return static::where('referred_id', $userId)->exists();
    }

    /**
     * Check if user can refer (hasn't reached limit)
     */
    public static function canUserRefer(int $userId): bool
    {
        $activeReferrals = static::where('referrer_id', $userId)
            ->active()
            ->count();

        // Default limit of 100 active referrals per user
        return $activeReferrals < 100;
    }

    /**
     * Scope for referrals by source
     */
    public function scopeBySource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    /**
     * Scope for referrals by campaign
     */
    public function scopeByCampaign(Builder $query, string $campaign): Builder
    {
        return $query->where('campaign', $campaign);
    }

    /**
     * Scope for referrals with rewards
     */
    public function scopeWithRewards(Builder $query): Builder
    {
        return $query->has('rewards');
    }

    /**
     * Scope for referrals without rewards
     */
    public function scopeWithoutRewards(Builder $query): Builder
    {
        return $query->doesntHave('rewards');
    }

    /**
     * Get total rewards amount for this referral
     */
    public function getTotalRewardsAmountAttribute(): float
    {
        return $this->rewards()->sum('amount');
    }

    /**
     * Get conversion rate for this referral
     */
    public function getConversionRateAttribute(): float
    {
        $totalOrders = $this->referredOrders()->count();
        return $totalOrders > 0 ? 100.0 : 0.0;
    }

    /**
     * Get days since creation
     */
    public function getDaysSinceCreatedAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Check if referral is about to expire (within 7 days)
     */
    public function isAboutToExpire(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isBefore(now()->addDays(7)) && $this->expires_at->isAfter(now());
    }

    /**
     * Get referral performance score (0-100)
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
        
        // Bonus for recent completion
        if ($this->completed_at && $this->completed_at->isAfter(now()->subDays(30))) {
            $score += 20;
        }
        
        // Bonus for orders from referred user
        $orderCount = $this->referredOrders()->count();
        $score += min($orderCount * 5, 10);
        
        return min($score, 100);
    }

    /**
     * Generate unique referral code
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Create referral with automatic code generation
     */
    public static function createWithCode(array $attributes): self
    {
        if (!isset($attributes['referral_code'])) {
            $attributes['referral_code'] = static::generateUniqueCode();
        }

        return static::create($attributes);
    }
}

