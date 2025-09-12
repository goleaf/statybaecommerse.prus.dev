<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Translatable\HasTranslations;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

final class ReferralCampaign extends Model
{
    use HasFactory, HasTranslations, LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'start_date',
        'end_date',
        'reward_amount',
        'reward_type',
        'max_referrals_per_user',
        'max_total_referrals',
        'conditions',
        'metadata',
    ];

    public array $translatable = [
        'name',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'reward_amount' => 'decimal:2',
            'max_referrals_per_user' => 'integer',
            'max_total_referrals' => 'integer',
            'conditions' => 'array',
            'metadata' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'is_active', 'start_date', 'end_date', 'reward_amount', 'reward_type'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get referral codes for this campaign
     */
    public function referralCodes(): HasMany
    {
        return $this->hasMany(ReferralCode::class, 'campaign_id');
    }

    /**
     * Scope for active campaigns
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Check if campaign is currently active
     */
    public function isActive(): bool
    {
        if (!$this->is_active) {
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
     * Get localized name
     */
    public function getLocalizedNameAttribute(): string
    {
        return $this->getTranslation('name', app()->getLocale()) ?: $this->name;
    }

    /**
     * Get localized description
     */
    public function getLocalizedDescriptionAttribute(): string
    {
        return $this->getTranslation('description', app()->getLocale()) ?: $this->description;
    }
}

