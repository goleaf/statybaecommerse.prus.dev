<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\DateRangeScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

/**
 * ReferralCampaign
 *
 * Eloquent model representing the ReferralCampaign entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property int                               $id
 * @property array<string, string>|string      $name
 * @property array<string, string>|string|null $description
 * @property bool                              $is_active
 * @property \Illuminate\Support\Carbon|null   $start_date
 * @property \Illuminate\Support\Carbon|null   $end_date
 * @property float|null                        $reward_amount
 * @property string                            $reward_type
 * @property int|null                          $max_referrals_per_user
 * @property int|null                          $max_total_referrals
 * @property array<string, mixed>|null         $conditions
 * @property array<string, mixed>|null         $metadata
 * @property \Illuminate\Support\Carbon|null   $created_at
 * @property \Illuminate\Support\Carbon|null   $updated_at
 * @property-read string $localizedName
 * @property-read string $localizedDescription
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ReferralCode> $referralCodes
 * @property-read int|null $referral_codes_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static> newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static> newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static> query()
 * @method static \Illuminate\Database\Eloquent\Builder<static> active()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, DateRangeScope::class])]
final class ReferralCampaign extends Model
{
    /** @use HasFactory<\Database\Factories\ReferralCampaignFactory> */
    use HasFactory;

    use HasTranslations;
    use LogsActivity;
    use OrdersByName;

    protected $fillable = ['name', 'description', 'is_active', 'start_date', 'end_date', 'reward_amount', 'reward_type', 'max_referrals_per_user', 'max_total_referrals', 'conditions', 'metadata'];

    /** @var array<int, string> */
    public array $translatable = ['name', 'description'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'start_date' => 'datetime', 'end_date' => 'datetime', 'reward_amount' => 'float', 'max_referrals_per_user' => 'integer', 'max_total_referrals' => 'integer', 'conditions' => 'array', 'metadata' => 'array'];
    }

    /**
     * Handle getActivitylogOptions functionality with proper error handling.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'description', 'is_active', 'start_date', 'end_date', 'reward_amount', 'reward_type'])->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    /**
     * @return HasMany<ReferralCode, $this>
     */
    public function referralCodes(): HasMany
    {
        return $this->hasMany(ReferralCode::class, 'campaign_id');
    }

    /**
     * Scope to get only active campaigns within their date range.
     *
     * @param  Builder<static> $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where(function (Builder $q): void {
            $q->whereNull('start_date')->orWhere('start_date', '<=', now());
        })->where(function (Builder $q): void {
            $q->whereNull('end_date')->orWhere('end_date', '>=', now());
        });
    }

    /**
     * Handle isActive functionality with proper error handling.
     */
    public function isActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->start_date && $this->start_date->isFuture()) {
            return false;
        }
        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the campaign is currently running (active and within date range).
     */
    public function isRunning(): bool
    {
        return $this->isActive();
    }

    /**
     * Check if the campaign has expired.
     */
    public function isExpired(): bool
    {
        if ($this->end_date === null) {
            return false;
        }

        return $this->end_date->isPast();
    }

    /**
     * Check if the campaign has started.
     */
    public function hasStarted(): bool
    {
        if ($this->start_date === null) {
            return true;
        }

        return $this->start_date->isPast() || $this->start_date->isToday();
    }

    /**
     * Check if the campaign can accept referrals.
     */
    public function canAcceptReferrals(): bool
    {
        if (! $this->isRunning()) {
            return false;
        }

        // Check if maximum total referrals limit is reached
        if ($this->max_total_referrals !== null) {
            $totalReferrals = $this->referralCodes()->count();
            if ($totalReferrals >= $this->max_total_referrals) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the reward amount for this campaign.
     */
    public function getRewardAmount(): float
    {
        return $this->reward_amount ?? 0.0;
    }

    /**
     * Get the remaining number of referrals available for this campaign.
     */
    public function getRemainingReferrals(): ?int
    {
        if ($this->max_total_referrals === null) {
            return null;
        }

        $usedReferrals = $this->referralCodes()->count();

        return max(0, $this->max_total_referrals - $usedReferrals);
    }

    /**
     * Get the localized name attribute.
     */
    public function getLocalizedNameAttribute(): string
    {
        $locale = (string) app()->getLocale();
        $fallbackLocale = (string) config('app.fallback_locale', 'en');
        $translated = $this->getTranslation('name', $locale);

        if (is_string($translated) && $translated !== '') {
            return $translated;
        }

        $fallback = $this->getTranslation('name', $fallbackLocale);
        if (is_string($fallback) && $fallback !== '') {
            return $fallback;
        }

        $raw = $this->getAttribute('name');

        /** @phpstan-ignore-next-line Safe type casting for translation fallback */
        return is_array($raw) ? (string) reset($raw) : (string) $raw;
    }

    /**
     * Get the localized description attribute.
     */
    public function getLocalizedDescriptionAttribute(): string
    {
        $locale = (string) app()->getLocale();
        $fallbackLocale = (string) config('app.fallback_locale', 'en');
        $translated = $this->getTranslation('description', $locale);

        if (is_string($translated) && $translated !== '') {
            return $translated;
        }

        $fallback = $this->getTranslation('description', $fallbackLocale);
        if (is_string($fallback) && $fallback !== '') {
            return $fallback;
        }

        $raw = $this->getAttribute('description');

        /** @phpstan-ignore-next-line Safe type casting for translation fallback */
        return is_array($raw) ? (string) reset($raw) : (string) $raw;
    }
}
