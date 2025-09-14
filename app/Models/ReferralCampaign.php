<?php

declare (strict_types=1);
namespace App\Models;

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
 * @property mixed $fillable
 * @property array $translatable
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralCampaign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralCampaign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralCampaign query()
 * @mixin \Eloquent
 */
final class ReferralCampaign extends Model
{
    use HasFactory, HasTranslations, LogsActivity;
    protected $fillable = ['name', 'description', 'is_active', 'start_date', 'end_date', 'reward_amount', 'reward_type', 'max_referrals_per_user', 'max_total_referrals', 'conditions', 'metadata'];
    public array $translatable = ['name', 'description'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'start_date' => 'datetime', 'end_date' => 'datetime', 'reward_amount' => 'decimal:2', 'max_referrals_per_user' => 'integer', 'max_total_referrals' => 'integer', 'conditions' => 'array', 'metadata' => 'array'];
    }
    /**
     * Handle getActivitylogOptions functionality with proper error handling.
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'description', 'is_active', 'start_date', 'end_date', 'reward_amount', 'reward_type'])->logOnlyDirty()->dontSubmitEmptyLogs();
    }
    /**
     * Handle referralCodes functionality with proper error handling.
     * @return HasMany
     */
    public function referralCodes(): HasMany
    {
        return $this->hasMany(ReferralCode::class, 'campaign_id');
    }
    /**
     * Handle scopeActive functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where(function ($q) {
            $q->whereNull('start_date')->orWhere('start_date', '<=', now());
        })->where(function ($q) {
            $q->whereNull('end_date')->orWhere('end_date', '>=', now());
        });
    }
    /**
     * Handle isActive functionality with proper error handling.
     * @return bool
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
     * Handle getLocalizedNameAttribute functionality with proper error handling.
     * @return string
     */
    public function getLocalizedNameAttribute(): string
    {
        return $this->getTranslation('name', app()->getLocale()) ?: $this->name;
    }
    /**
     * Handle getLocalizedDescriptionAttribute functionality with proper error handling.
     * @return string
     */
    public function getLocalizedDescriptionAttribute(): string
    {
        return $this->getTranslation('description', app()->getLocale()) ?: $this->description;
    }
}