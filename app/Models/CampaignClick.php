<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CampaignClick extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'campaign_clicks';

    protected $fillable = [
        'campaign_id',
        'session_id',
        'ip_address',
        'user_agent',
        'click_type',
        'clicked_url',
        'customer_id',
        'clicked_at',
        'referer',
        'device_type',
        'browser',
        'os',
        'country',
        'city',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'conversion_value',
        'is_converted',
        'conversion_data',
    ];

    protected function casts(): array
    {
        return [
            'clicked_at' => 'datetime',
            'conversion_value' => 'decimal:2',
            'is_converted' => 'boolean',
            'conversion_data' => 'array',
        ];
    }

    protected string $translationModel = \App\Models\Translations\CampaignClickTranslation::class;

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(CampaignConversion::class, 'click_id');
    }

    public function scopeByCampaign(Builder $query, int $campaignId): Builder
    {
        return $query->where('campaign_id', $campaignId);
    }

    public function scopeByCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByClickType(Builder $query, string $clickType): Builder
    {
        return $query->where('click_type', $clickType);
    }

    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('clicked_at', [$startDate, $endDate]);
    }

    public function scopeConverted(Builder $query): Builder
    {
        return $query->where('is_converted', true);
    }

    public function scopeByDeviceType(Builder $query, string $deviceType): Builder
    {
        return $query->where('device_type', $deviceType);
    }

    public function scopeByCountry(Builder $query, string $country): Builder
    {
        return $query->where('country', $country);
    }

    public function scopeByUtmSource(Builder $query, string $utmSource): Builder
    {
        return $query->where('utm_source', $utmSource);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('clicked_at', '>=', now()->subDays($days));
    }

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

    public function getDeviceTypeLabelAttribute(): string
    {
        return match ($this->device_type) {
            'desktop' => __('campaign_clicks.device_type.desktop'),
            'mobile' => __('campaign_clicks.device_type.mobile'),
            'tablet' => __('campaign_clicks.device_type.tablet'),
            default => __('campaign_clicks.device_type.unknown'),
        };
    }

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

    public function isConverted(): bool
    {
        return $this->is_converted;
    }

    public function getConversionRate(): float
    {
        if ($this->conversions()->count() === 0) {
            return 0;
        }

        return 100.0; // This click has conversions
    }

    public function getTotalConversionValue(): float
    {
        return $this->conversions()->sum('conversion_value');
    }

    public function getUtmParams(): array
    {
        return [
            'utm_source' => $this->utm_source,
            'utm_medium' => $this->utm_medium,
            'utm_campaign' => $this->utm_campaign,
            'utm_term' => $this->utm_term,
            'utm_content' => $this->utm_content,
        ];
    }

    public function getLocationInfo(): array
    {
        return [
            'country' => $this->country,
            'city' => $this->city,
            'ip_address' => $this->ip_address,
        ];
    }

    public function getDeviceInfo(): array
    {
        return [
            'device_type' => $this->device_type,
            'browser' => $this->browser,
            'os' => $this->os,
            'user_agent' => $this->user_agent,
        ];
    }
}
