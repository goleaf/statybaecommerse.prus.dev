<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
/**
 * CampaignClick
 * 
 * Eloquent model representing the CampaignClick entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $timestamps
 * @property mixed $table
 * @property mixed $fillable
 * @property string $translationModel
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignClick newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignClick newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignClick query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class CampaignClick extends Model
{
    use HasFactory, HasTranslations;
    public $timestamps = false;
    protected $table = 'campaign_clicks';
    protected $fillable = ['campaign_id', 'session_id', 'ip_address', 'user_agent', 'click_type', 'clicked_url', 'customer_id', 'clicked_at', 'referer', 'device_type', 'browser', 'os', 'country', 'city', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'conversion_value', 'is_converted', 'conversion_data'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['clicked_at' => 'datetime', 'conversion_value' => 'decimal:2', 'is_converted' => 'boolean', 'conversion_data' => 'array'];
    }
    protected string $translationModel = \App\Models\Translations\CampaignClickTranslation::class;
    /**
     * Handle campaign functionality with proper error handling.
     * @return BelongsTo
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
    /**
     * Handle customer functionality with proper error handling.
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
    /**
     * Handle conversions functionality with proper error handling.
     * @return HasMany
     */
    public function conversions(): HasMany
    {
        return $this->hasMany(CampaignConversion::class, 'click_id');
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
     * Handle scopeByCustomer functionality with proper error handling.
     * @param Builder $query
     * @param int $customerId
     * @return Builder
     */
    public function scopeByCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }
    /**
     * Handle scopeByClickType functionality with proper error handling.
     * @param Builder $query
     * @param string $clickType
     * @return Builder
     */
    public function scopeByClickType(Builder $query, string $clickType): Builder
    {
        return $query->where('click_type', $clickType);
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
        return $query->whereBetween('clicked_at', [$startDate, $endDate]);
    }
    /**
     * Handle scopeConverted functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeConverted(Builder $query): Builder
    {
        return $query->where('is_converted', true);
    }
    /**
     * Handle scopeByDeviceType functionality with proper error handling.
     * @param Builder $query
     * @param string $deviceType
     * @return Builder
     */
    public function scopeByDeviceType(Builder $query, string $deviceType): Builder
    {
        return $query->where('device_type', $deviceType);
    }
    /**
     * Handle scopeByCountry functionality with proper error handling.
     * @param Builder $query
     * @param string $country
     * @return Builder
     */
    public function scopeByCountry(Builder $query, string $country): Builder
    {
        return $query->where('country', $country);
    }
    /**
     * Handle scopeByUtmSource functionality with proper error handling.
     * @param Builder $query
     * @param string $utmSource
     * @return Builder
     */
    public function scopeByUtmSource(Builder $query, string $utmSource): Builder
    {
        return $query->where('utm_source', $utmSource);
    }
    /**
     * Handle scopeRecent functionality with proper error handling.
     * @param Builder $query
     * @param int $days
     * @return Builder
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('clicked_at', '>=', now()->subDays($days));
    }
    /**
     * Handle getClickTypeLabelAttribute functionality with proper error handling.
     * @return string
     */
    public function getClickTypeLabelAttribute(): string
    {
        return match ($this->click_type) {
            'cta' => __('campaign_clicks.click_type.cta'),
            'banner' => __('campaign_clicks.click_type.banner'),
            'link' => __('campaign_clicks.click_type.link'),
            'button' => __('campaign_clicks.click_type.button'),
            'image' => __('campaign_clicks.click_type.image'),
            default => __('campaign_clicks.click_type.unknown'),
        };
    }
    /**
     * Handle getDeviceTypeLabelAttribute functionality with proper error handling.
     * @return string
     */
    public function getDeviceTypeLabelAttribute(): string
    {
        return match ($this->device_type) {
            'desktop' => __('campaign_clicks.device_type.desktop'),
            'mobile' => __('campaign_clicks.device_type.mobile'),
            'tablet' => __('campaign_clicks.device_type.tablet'),
            default => __('campaign_clicks.device_type.unknown'),
        };
    }
    /**
     * Handle getBrowserLabelAttribute functionality with proper error handling.
     * @return string
     */
    public function getBrowserLabelAttribute(): string
    {
        return match ($this->browser) {
            'chrome' => __('campaign_clicks.browser.chrome'),
            'firefox' => __('campaign_clicks.browser.firefox'),
            'safari' => __('campaign_clicks.browser.safari'),
            'edge' => __('campaign_clicks.browser.edge'),
            'opera' => __('campaign_clicks.browser.opera'),
            default => $this->browser ?? __('campaign_clicks.browser.unknown'),
        };
    }
    /**
     * Handle getOsLabelAttribute functionality with proper error handling.
     * @return string
     */
    public function getOsLabelAttribute(): string
    {
        return match ($this->os) {
            'windows' => __('campaign_clicks.os.windows'),
            'macos' => __('campaign_clicks.os.macos'),
            'linux' => __('campaign_clicks.os.linux'),
            'android' => __('campaign_clicks.os.android'),
            'ios' => __('campaign_clicks.os.ios'),
            default => $this->os ?? __('campaign_clicks.os.unknown'),
        };
    }
    /**
     * Handle isConverted functionality with proper error handling.
     * @return bool
     */
    public function isConverted(): bool
    {
        return $this->is_converted;
    }
    /**
     * Handle getConversionRate functionality with proper error handling.
     * @return float
     */
    public function getConversionRate(): float
    {
        if ($this->conversions()->count() === 0) {
            return 0;
        }
        return 100.0;
        // This click has conversions
    }
    /**
     * Handle getTotalConversionValue functionality with proper error handling.
     * @return float
     */
    public function getTotalConversionValue(): float
    {
        return $this->conversions()->sum('conversion_value');
    }
    /**
     * Handle getUtmParams functionality with proper error handling.
     * @return array
     */
    public function getUtmParams(): array
    {
        return ['utm_source' => $this->utm_source, 'utm_medium' => $this->utm_medium, 'utm_campaign' => $this->utm_campaign, 'utm_term' => $this->utm_term, 'utm_content' => $this->utm_content];
    }
    /**
     * Handle getLocationInfo functionality with proper error handling.
     * @return array
     */
    public function getLocationInfo(): array
    {
        return ['country' => $this->country, 'city' => $this->city, 'ip_address' => $this->ip_address];
    }
    /**
     * Handle getDeviceInfo functionality with proper error handling.
     * @return array
     */
    public function getDeviceInfo(): array
    {
        return ['device_type' => $this->device_type, 'browser' => $this->browser, 'os' => $this->os, 'user_agent' => $this->user_agent];
    }
}