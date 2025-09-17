<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveCampaignScope;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\StatusScope;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
/**
 * Campaign
 * 
 * Eloquent model representing the Campaign entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @property string $translationModel
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, StatusScope::class, ActiveCampaignScope::class])]
final class Campaign extends Model
{
    use HasFactory, SoftDeletes;
    use HasTranslations;
    protected $table = 'discount_campaigns';
    protected $fillable = ['name', 'slug', 'starts_at', 'ends_at', 'channel_id', 'zone_id', 'status', 'is_active', 'metadata', 'is_featured', 'send_notifications', 'track_conversions', 'max_uses', 'budget_limit'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'start_date' => 'datetime', 'end_date' => 'datetime', 'budget' => 'decimal:2', 'metadata' => 'array', 'is_active' => 'boolean', 'is_featured' => 'boolean', 'send_notifications' => 'boolean', 'track_conversions' => 'boolean', 'max_uses' => 'integer', 'budget_limit' => 'decimal:2', 'total_views' => 'integer', 'total_clicks' => 'integer', 'total_conversions' => 'integer', 'total_revenue' => 'decimal:2', 'conversion_rate' => 'decimal:2', 'target_audience' => 'array', 'target_categories' => 'array', 'target_products' => 'array', 'target_customer_groups' => 'array', 'target_segments' => 'array', 'display_priority' => 'integer', 'auto_start' => 'boolean', 'auto_end' => 'boolean', 'auto_pause_on_budget' => 'boolean', 'social_media_ready' => 'boolean'];
    }
    protected string $translationModel = \App\Models\Translations\CampaignTranslation::class;
    /**
     * Handle getRouteKeyName functionality with proper error handling.
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
    /**
     * Handle getTranslatedName functionality with proper error handling.
     * @param string|null $locale
     * @return string
     */
    public function getTranslatedName(?string $locale = null): string
    {
        if ($locale === null) {
            return $this->name;
        }
        // Load translations if not already loaded
        if (!$this->relationLoaded('translations')) {
            $this->load('translations');
        }
        $translation = $this->translations->firstWhere('locale', $locale);
        if ($translation && !empty($translation->name)) {
            return $translation->name;
        }
        // If no translation found for the specific locale, return the model's name
        return $this->name;
    }
    /**
     * Handle discounts functionality with proper error handling.
     * @return BelongsToMany
     */
    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'campaign_discount');
    }
    /**
     * Handle channel functionality with proper error handling.
     * @return BelongsTo
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }
    /**
     * Handle zone functionality with proper error handling.
     * @return BelongsTo
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
    /**
     * Handle views functionality with proper error handling.
     * @return HasMany
     */
    public function views(): HasMany
    {
        return $this->hasMany(CampaignView::class);
    }
    /**
     * Handle clicks functionality with proper error handling.
     * @return HasMany
     */
    public function clicks(): HasMany
    {
        return $this->hasMany(CampaignClick::class);
    }
    /**
     * Handle conversions functionality with proper error handling.
     * @return HasMany
     */
    public function conversions(): HasMany
    {
        return $this->hasMany(CampaignConversion::class);
    }
    /**
     * Handle customerSegments functionality with proper error handling.
     * @return HasMany
     */
    public function customerSegments(): HasMany
    {
        return $this->hasMany(CampaignCustomerSegment::class);
    }
    /**
     * Handle productTargets functionality with proper error handling.
     * @return HasMany
     */
    public function productTargets(): HasMany
    {
        return $this->hasMany(CampaignProductTarget::class);
    }
    /**
     * Handle schedules functionality with proper error handling.
     * @return HasMany
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(CampaignSchedule::class);
    }
    /**
     * Handle latestView functionality with proper error handling.
     * @return HasOne
     */
    public function latestView(): HasOne
    {
        return $this->views()->one()->ofMany('viewed_at', 'max');
    }
    /**
     * Handle latestClick functionality with proper error handling.
     * @return HasOne
     */
    public function latestClick(): HasOne
    {
        return $this->clicks()->one()->ofMany('clicked_at', 'max');
    }
    /**
     * Handle latestConversion functionality with proper error handling.
     * @return HasOne
     */
    public function latestConversion(): HasOne
    {
        return $this->conversions()->one()->ofMany('converted_at', 'max');
    }
    /**
     * Handle highestValueConversion functionality with proper error handling.
     * @return HasOne
     */
    public function highestValueConversion(): HasOne
    {
        return $this->conversions()->one()->ofMany('conversion_value', 'max');
    }
    /**
     * Handle lowestValueConversion functionality with proper error handling.
     * @return HasOne
     */
    public function lowestValueConversion(): HasOne
    {
        return $this->conversions()->one()->ofMany('conversion_value', 'min');
    }
    /**
     * Handle latestSchedule functionality with proper error handling.
     * @return HasOne
     */
    public function latestSchedule(): HasOne
    {
        return $this->schedules()->one()->latestOfMany();
    }
    /**
     * Handle orders functionality with proper error handling.
     * @return HasManyThrough
     */
    public function orders(): HasManyThrough
    {
        return $this->hasManyThrough(Order::class, CampaignConversion::class);
    }
    /**
     * Handle latestOrder functionality with proper error handling.
     * @return HasOneThrough
     */
    public function latestOrder(): HasOneThrough
    {
        return $this->orders()->one()->latestOfMany();
    }
    /**
     * Handle targetCategories functionality with proper error handling.
     * @return BelongsToMany
     */
    public function targetCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'campaign_categories');
    }
    /**
     * Handle targetProducts functionality with proper error handling.
     * @return BelongsToMany
     */
    public function targetProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'campaign_products');
    }
    /**
     * Handle targetCustomerGroups functionality with proper error handling.
     * @return BelongsToMany
     */
    public function targetCustomerGroups(): BelongsToMany
    {
        return $this->belongsToMany(CustomerGroup::class, 'campaign_customer_groups');
    }
    /**
     * Handle scopeActive functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->where(function ($q) {
            $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
        })->where(function ($q) {
            $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
        });
    }
    /**
     * Handle scopeScheduled functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }
    /**
     * Handle scopeExpired functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired')->orWhere(function ($q) {
            $q->whereNotNull('ends_at')->where('ends_at', '<', now());
        });
    }
    /**
     * Handle scopeFeatured functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
    /**
     * Handle scopeByPriority functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeByPriority(Builder $query): Builder
    {
        return $query->orderBy('display_priority', 'desc');
    }
    /**
     * Handle scopeForChannel functionality with proper error handling.
     * @param Builder $query
     * @param int $channelId
     * @return Builder
     */
    public function scopeForChannel(Builder $query, int $channelId): Builder
    {
        return $query->where('channel_id', $channelId);
    }
    /**
     * Handle scopeForZone functionality with proper error handling.
     * @param Builder $query
     * @param int $zoneId
     * @return Builder
     */
    public function scopeForZone(Builder $query, int $zoneId): Builder
    {
        return $query->where('zone_id', $zoneId);
    }
    /**
     * Handle scopeWithAnalytics functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithAnalytics(Builder $query): Builder
    {
        return $query->where('track_conversions', true);
    }
    /**
     * Handle scopeSocialMediaReady functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeSocialMediaReady(Builder $query): Builder
    {
        return $query->where('social_media_ready', true);
    }
    /**
     * Handle isActive functionality with proper error handling.
     * @return bool
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        $now = now();
        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }
        if ($this->ends_at && $this->ends_at->lt($now)) {
            return false;
        }
        return true;
    }
    /**
     * Handle isExpired functionality with proper error handling.
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->ends_at && $this->ends_at->lt(now());
    }
    /**
     * Handle isScheduled functionality with proper error handling.
     * @return bool
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled' && $this->starts_at && $this->starts_at->gt(now());
    }
    /**
     * Handle isInactive functionality with proper error handling.
     * @return bool
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive' || !$this->isActive();
    }
    /**
     * Handle isUpcoming functionality with proper error handling.
     * @return bool
     */
    public function isUpcoming(): bool
    {
        return $this->starts_at && $this->starts_at->gt(now());
    }
    /**
     * Handle isWithinBudget functionality with proper error handling.
     * @return bool
     */
    public function isWithinBudget(): bool
    {
        if (!$this->budget_limit) {
            return true;
        }
        return $this->total_revenue < $this->budget_limit;
    }
    /**
     * Handle getClickThroughRate functionality with proper error handling.
     * @return float
     */
    public function getClickThroughRate(): float
    {
        if ($this->total_views === 0) {
            return 0;
        }
        return round($this->total_clicks / $this->total_views * 100, 2);
    }
    /**
     * Handle getConversionRate functionality with proper error handling.
     * @return float
     */
    public function getConversionRate(): float
    {
        if ($this->total_clicks === 0) {
            return 0;
        }
        return round($this->total_conversions / $this->total_clicks * 100, 2);
    }
    /**
     * Handle getROI functionality with proper error handling.
     * @return float
     */
    public function getROI(): float
    {
        if ($this->budget_limit === 0) {
            return 0;
        }
        return round(($this->total_revenue - $this->budget_limit) / $this->budget_limit * 100, 2);
    }
    /**
     * Handle recordView functionality with proper error handling.
     * @param string|null $sessionId
     * @param string|null $ipAddress
     * @param string|null $userAgent
     * @param string|null $referer
     * @param int|null $customerId
     * @return void
     */
    public function recordView(?string $sessionId = null, ?string $ipAddress = null, ?string $userAgent = null, ?string $referer = null, ?int $customerId = null): void
    {
        $this->views()->create(['session_id' => $sessionId, 'ip_address' => $ipAddress, 'user_agent' => $userAgent, 'referer' => $referer, 'customer_id' => $customerId, 'viewed_at' => now()]);
        $metadata = $this->metadata ?? [];
        $metadata['total_views'] = ($metadata['total_views'] ?? 0) + 1;
        $this->update(['metadata' => $metadata]);
    }
    /**
     * Handle recordClick functionality with proper error handling.
     * @param string $clickType
     * @param string|null $clickedUrl
     * @param string|null $sessionId
     * @param string|null $ipAddress
     * @param string|null $userAgent
     * @param int|null $customerId
     * @return void
     */
    public function recordClick(string $clickType = 'cta', ?string $clickedUrl = null, ?string $sessionId = null, ?string $ipAddress = null, ?string $userAgent = null, ?int $customerId = null): void
    {
        $this->clicks()->create(['session_id' => $sessionId, 'ip_address' => $ipAddress, 'user_agent' => $userAgent, 'click_type' => $clickType, 'clicked_url' => $clickedUrl, 'customer_id' => $customerId, 'clicked_at' => now()]);
        $metadata = $this->metadata ?? [];
        $metadata['total_clicks'] = ($metadata['total_clicks'] ?? 0) + 1;
        $this->update(['metadata' => $metadata]);
    }
    /**
     * Handle recordConversion functionality with proper error handling.
     * @param string $conversionType
     * @param float $conversionValue
     * @param int|null $orderId
     * @param int|null $customerId
     * @param string|null $sessionId
     * @param array $conversionData
     * @return void
     */
    public function recordConversion(string $conversionType = 'purchase', float $conversionValue = 0, ?int $orderId = null, ?int $customerId = null, ?string $sessionId = null, array $conversionData = []): void
    {
        $this->conversions()->create(['order_id' => $orderId, 'customer_id' => $customerId, 'conversion_type' => $conversionType, 'conversion_value' => $conversionValue, 'session_id' => $sessionId, 'conversion_data' => $conversionData, 'converted_at' => now()]);
        $metadata = $this->metadata ?? [];
        $metadata['total_conversions'] = ($metadata['total_conversions'] ?? 0) + 1;
        $metadata['total_revenue'] = ($metadata['total_revenue'] ?? 0) + $conversionValue;
        $metadata['conversion_rate'] = $this->getConversionRate();
        $this->update(['metadata' => $metadata]);
    }
    /**
     * Handle getBannerUrl functionality with proper error handling.
     * @return string|null
     */
    public function getBannerUrl(): ?string
    {
        if (!$this->banner_image) {
            return null;
        }
        return asset('storage/campaigns/' . $this->banner_image);
    }
    /**
     * Handle getStatusBadgeColor functionality with proper error handling.
     * @return string
     */
    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'scheduled' => 'warning',
            'paused' => 'secondary',
            'expired' => 'danger',
            'draft' => 'info',
            default => 'secondary',
        };
    }
    /**
     * Handle getStatusLabel functionality with proper error handling.
     * @return string
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'active' => __('campaigns.status.active'),
            'scheduled' => __('campaigns.status.scheduled'),
            'paused' => __('campaigns.status.paused'),
            'expired' => __('campaigns.status.expired'),
            'draft' => __('campaigns.status.draft'),
            default => __('campaigns.status.unknown'),
        };
    }
    /**
     * Handle registerMediaCollections functionality with proper error handling.
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])->singleFile();
        $this->addMediaCollection('banners')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])->singleFile();
        $this->addMediaCollection('attachments')->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }
    /**
     * Handle registerMediaConversions functionality with proper error handling.
     * @param Media|null $media
     * @return void
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(150)->height(150)->sharpen(10)->performOnCollections('images', 'banners');
        $this->addMediaConversion('medium')->width(400)->height(300)->sharpen(10)->performOnCollections('images', 'banners');
        $this->addMediaConversion('large')->width(800)->height(600)->sharpen(10)->performOnCollections('images', 'banners');
    }
    /**
     * Handle getImageUrl functionality with proper error handling.
     * @param string $conversion
     * @return string|null
     */
    public function getImageUrl(string $conversion = ''): ?string
    {
        $media = $this->getFirstMedia('images');
        return $media ? $media->getUrl($conversion) : null;
    }
    // Additional helper methods and accessors
    /**
     * Handle getDisplayNameAttribute functionality with proper error handling.
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->slug;
    }
    // Accessors for metadata fields
    /**
     * Handle getDescriptionAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getDescriptionAttribute(): ?string
    {
        return $this->metadata['description'] ?? null;
    }
    /**
     * Handle getTypeAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getTypeAttribute(): ?string
    {
        return $this->metadata['type'] ?? 'banner';
    }
    /**
     * Handle getSubjectAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getSubjectAttribute(): ?string
    {
        return $this->metadata['subject'] ?? null;
    }
    /**
     * Handle getContentAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getContentAttribute(): ?string
    {
        return $this->metadata['content'] ?? null;
    }
    /**
     * Handle getStartDateAttribute functionality with proper error handling.
     * @return Carbon\Carbon|null
     */
    public function getStartDateAttribute(): ?\Carbon\Carbon
    {
        return $this->starts_at;
    }
    /**
     * Handle getEndDateAttribute functionality with proper error handling.
     * @return Carbon\Carbon|null
     */
    public function getEndDateAttribute(): ?\Carbon\Carbon
    {
        return $this->ends_at;
    }
    /**
     * Handle getBudgetAttribute functionality with proper error handling.
     * @return float|null
     */
    public function getBudgetAttribute(): ?float
    {
        return $this->metadata['budget'] ?? null;
    }
    /**
     * Handle getTotalViewsAttribute functionality with proper error handling.
     * @return int
     */
    public function getTotalViewsAttribute(): int
    {
        return $this->metadata['total_views'] ?? 0;
    }
    /**
     * Handle getTotalClicksAttribute functionality with proper error handling.
     * @return int
     */
    public function getTotalClicksAttribute(): int
    {
        return $this->metadata['total_clicks'] ?? 0;
    }
    /**
     * Handle getTotalConversionsAttribute functionality with proper error handling.
     * @return int
     */
    public function getTotalConversionsAttribute(): int
    {
        return $this->metadata['total_conversions'] ?? 0;
    }
    /**
     * Handle getTotalRevenueAttribute functionality with proper error handling.
     * @return float
     */
    public function getTotalRevenueAttribute(): float
    {
        return $this->metadata['total_revenue'] ?? 0;
    }
    /**
     * Handle getConversionRateAttribute functionality with proper error handling.
     * @return float
     */
    public function getConversionRateAttribute(): float
    {
        return $this->metadata['conversion_rate'] ?? 0;
    }
    /**
     * Handle getTargetAudienceAttribute functionality with proper error handling.
     * @return array|null
     */
    public function getTargetAudienceAttribute(): ?array
    {
        return $this->metadata['target_audience'] ?? null;
    }
    /**
     * Handle getTargetSegmentsAttribute functionality with proper error handling.
     * @return array|null
     */
    public function getTargetSegmentsAttribute(): ?array
    {
        return $this->metadata['target_segments'] ?? null;
    }
    /**
     * Handle getDisplayPriorityAttribute functionality with proper error handling.
     * @return int
     */
    public function getDisplayPriorityAttribute(): int
    {
        return $this->metadata['display_priority'] ?? 0;
    }
    /**
     * Handle getBannerImageAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getBannerImageAttribute(): ?string
    {
        return $this->metadata['banner_image'] ?? null;
    }
    /**
     * Handle getBannerAltTextAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getBannerAltTextAttribute(): ?string
    {
        return $this->metadata['banner_alt_text'] ?? null;
    }
    /**
     * Handle getCtaTextAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getCtaTextAttribute(): ?string
    {
        return $this->metadata['cta_text'] ?? null;
    }
    /**
     * Handle getCtaUrlAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getCtaUrlAttribute(): ?string
    {
        return $this->metadata['cta_url'] ?? null;
    }
    /**
     * Handle getAutoStartAttribute functionality with proper error handling.
     * @return bool
     */
    public function getAutoStartAttribute(): bool
    {
        return $this->metadata['auto_start'] ?? false;
    }
    /**
     * Handle getAutoEndAttribute functionality with proper error handling.
     * @return bool
     */
    public function getAutoEndAttribute(): bool
    {
        return $this->metadata['auto_end'] ?? false;
    }
    /**
     * Handle getAutoPauseOnBudgetAttribute functionality with proper error handling.
     * @return bool
     */
    public function getAutoPauseOnBudgetAttribute(): bool
    {
        return $this->metadata['auto_pause_on_budget'] ?? false;
    }
    /**
     * Handle getMetaTitleAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getMetaTitleAttribute(): ?string
    {
        return $this->metadata['meta_title'] ?? null;
    }
    /**
     * Handle getMetaDescriptionAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getMetaDescriptionAttribute(): ?string
    {
        return $this->metadata['meta_description'] ?? null;
    }
    /**
     * Handle getSocialMediaReadyAttribute functionality with proper error handling.
     * @return bool
     */
    public function getSocialMediaReadyAttribute(): bool
    {
        return $this->metadata['social_media_ready'] ?? false;
    }
    /**
     * Handle getFormattedDescriptionAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedDescriptionAttribute(): string
    {
        return $this->description ? strip_tags($this->description) : '';
    }
    /**
     * Handle getTypeIconAttribute functionality with proper error handling.
     * @return string
     */
    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'email' => 'heroicon-o-envelope',
            'sms' => 'heroicon-o-device-phone-mobile',
            'push' => 'heroicon-o-bell',
            'banner' => 'heroicon-o-photo',
            'popup' => 'heroicon-o-window',
            'social' => 'heroicon-o-share',
            default => 'heroicon-o-megaphone',
        };
    }
    /**
     * Handle getTypeColorAttribute functionality with proper error handling.
     * @return string
     */
    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'email' => 'blue',
            'sms' => 'green',
            'push' => 'yellow',
            'banner' => 'purple',
            'popup' => 'pink',
            'social' => 'red',
            default => 'gray',
        };
    }
    /**
     * Handle getTypeLabelAttribute functionality with proper error handling.
     * @return string
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'email' => __('campaigns.types.email'),
            'sms' => __('campaigns.types.sms'),
            'push' => __('campaigns.types.push'),
            'banner' => __('campaigns.types.banner'),
            'popup' => __('campaigns.types.popup'),
            'social' => __('campaigns.types.social'),
            default => ucfirst($this->type),
        };
    }
    /**
     * Handle getDurationAttribute functionality with proper error handling.
     * @return int|null
     */
    public function getDurationAttribute(): ?int
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }
        return $this->start_date->diffInDays($this->end_date);
    }
    /**
     * Handle getDaysRemainingAttribute functionality with proper error handling.
     * @return int|null
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->end_date) {
            return null;
        }
        $remaining = now()->diffInDays($this->end_date, false);
        return $remaining > 0 ? $remaining : 0;
    }
    /**
     * Handle getProgressPercentageAttribute functionality with proper error handling.
     * @return float
     */
    public function getProgressPercentageAttribute(): float
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }
        $total = $this->start_date->diffInDays($this->end_date);
        $elapsed = $this->start_date->diffInDays(now());
        if ($total <= 0) {
            return 100;
        }
        return min(100, max(0, $elapsed / $total * 100));
    }
    /**
     * Handle getBudgetUtilizationAttribute functionality with proper error handling.
     * @return float
     */
    public function getBudgetUtilizationAttribute(): float
    {
        if (!$this->budget_limit || $this->budget_limit <= 0) {
            return 0;
        }
        return min(100, $this->total_revenue / $this->budget_limit * 100);
    }
    /**
     * Handle getPerformanceScoreAttribute functionality with proper error handling.
     * @return int
     */
    public function getPerformanceScoreAttribute(): int
    {
        $score = 0;
        // Base score from conversion rate
        $score += min(40, $this->getConversionRate() * 0.4);
        // Click-through rate contribution
        $score += min(30, $this->getClickThroughRate() * 0.3);
        // Budget utilization (efficient spending)
        if ($this->budget_limit > 0) {
            $utilization = $this->getBudgetUtilization();
            $score += min(20, $utilization * 0.2);
        }
        // ROI contribution
        $roi = $this->getROI();
        $score += min(10, max(0, $roi * 0.1));
        return min(100, max(0, round($score)));
    }
    /**
     * Handle getPerformanceGradeAttribute functionality with proper error handling.
     * @return string
     */
    public function getPerformanceGradeAttribute(): string
    {
        $score = $this->performance_score;
        return match (true) {
            $score >= 90 => 'A+',
            $score >= 80 => 'A',
            $score >= 70 => 'B+',
            $score >= 60 => 'B',
            $score >= 50 => 'C+',
            $score >= 40 => 'C',
            $score >= 30 => 'D',
            default => 'F',
        };
    }
    /**
     * Handle getPerformanceColorAttribute functionality with proper error handling.
     * @return string
     */
    public function getPerformanceColorAttribute(): string
    {
        $score = $this->performance_score;
        return match (true) {
            $score >= 80 => 'success',
            $score >= 60 => 'warning',
            $score >= 40 => 'info',
            default => 'danger',
        };
    }
    /**
     * Handle getStatistics functionality with proper error handling.
     * @return array
     */
    public function getStatistics(): array
    {
        return ['views' => $this->total_views, 'clicks' => $this->total_clicks, 'conversions' => $this->total_conversions, 'revenue' => $this->total_revenue, 'conversion_rate' => $this->getConversionRate(), 'click_through_rate' => $this->getClickThroughRate(), 'roi' => $this->getROI(), 'performance_score' => $this->performance_score, 'performance_grade' => $this->performance_grade, 'performance_color' => $this->performance_color, 'budget_utilization' => $this->budget_utilization, 'progress_percentage' => $this->progress_percentage, 'days_remaining' => $this->days_remaining, 'duration' => $this->duration];
    }
    /**
     * Handle getFormattedBudgetAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedBudgetAttribute(): string
    {
        return '€' . number_format($this->budget, 2);
    }
    /**
     * Handle getFormattedBudgetLimitAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedBudgetLimitAttribute(): string
    {
        return '€' . number_format($this->budget_limit, 2);
    }
    /**
     * Handle getFormattedTotalRevenueAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedTotalRevenueAttribute(): string
    {
        return '€' . number_format($this->total_revenue, 2);
    }
    /**
     * Handle getFormattedROIAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedROIAttribute(): string
    {
        return number_format($this->getROI(), 2) . '%';
    }
    /**
     * Handle getFormattedConversionRateAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedConversionRateAttribute(): string
    {
        return number_format($this->getConversionRate(), 2) . '%';
    }
    /**
     * Handle getFormattedClickThroughRateAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedClickThroughRateAttribute(): string
    {
        return number_format($this->getClickThroughRate(), 2) . '%';
    }
    /**
     * Handle getFormattedBudgetUtilizationAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedBudgetUtilizationAttribute(): string
    {
        return number_format($this->budget_utilization, 2) . '%';
    }
    /**
     * Handle getFormattedProgressPercentageAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedProgressPercentageAttribute(): string
    {
        return number_format($this->progress_percentage, 1) . '%';
    }
    /**
     * Handle isHighPerforming functionality with proper error handling.
     * @return bool
     */
    public function isHighPerforming(): bool
    {
        return $this->performance_score >= 80;
    }
    /**
     * Handle isUnderperforming functionality with proper error handling.
     * @return bool
     */
    public function isUnderperforming(): bool
    {
        return $this->performance_score < 40;
    }
    /**
     * Handle needsAttention functionality with proper error handling.
     * @return bool
     */
    public function needsAttention(): bool
    {
        return $this->isUnderperforming() || $this->budget_utilization > 90;
    }
    /**
     * Handle canBeActivated functionality with proper error handling.
     * @return bool
     */
    public function canBeActivated(): bool
    {
        return $this->status === 'draft' || $this->status === 'scheduled';
    }
    /**
     * Handle canBePaused functionality with proper error handling.
     * @return bool
     */
    public function canBePaused(): bool
    {
        return $this->status === 'active';
    }
    /**
     * Handle canBeResumed functionality with proper error handling.
     * @return bool
     */
    public function canBeResumed(): bool
    {
        return $this->status === 'paused';
    }
    /**
     * Handle canBeCompleted functionality with proper error handling.
     * @return bool
     */
    public function canBeCompleted(): bool
    {
        return $this->status === 'active' && $this->isExpired();
    }
    /**
     * Handle getRecommendedActions functionality with proper error handling.
     * @return array
     */
    public function getRecommendedActions(): array
    {
        $actions = [];
        if ($this->needsAttention()) {
            $actions[] = 'review_performance';
        }
        if ($this->budget_utilization > 80) {
            $actions[] = 'monitor_budget';
        }
        if ($this->getConversionRate() < 2) {
            $actions[] = 'optimize_content';
        }
        if ($this->getClickThroughRate() < 1) {
            $actions[] = 'improve_targeting';
        }
        if ($this->days_remaining && $this->days_remaining <= 7) {
            $actions[] = 'extend_campaign';
        }
        return $actions;
    }
    /**
     * Handle duplicateForNewPeriod functionality with proper error handling.
     * @param Carbon\Carbon $newStartDate
     * @param Carbon\Carbon $newEndDate
     * @return self
     */
    public function duplicateForNewPeriod(\Carbon\Carbon $newStartDate, \Carbon\Carbon $newEndDate): self
    {
        $duplicate = $this->replicate();
        $duplicate->name = $this->name . ' (Copy)';
        $duplicate->slug = $this->slug . '-copy-' . time();
        $duplicate->start_date = $newStartDate;
        $duplicate->end_date = $newEndDate;
        $duplicate->status = 'draft';
        $duplicate->total_views = 0;
        $duplicate->total_clicks = 0;
        $duplicate->total_conversions = 0;
        $duplicate->total_revenue = 0;
        $duplicate->conversion_rate = 0;
        $duplicate->save();
        return $duplicate;
    }
    /**
     * Handle getTargetingSummary functionality with proper error handling.
     * @return array
     */
    public function getTargetingSummary(): array
    {
        return ['categories_count' => $this->targetCategories()->count(), 'products_count' => $this->targetProducts()->count(), 'customer_groups_count' => $this->targetCustomerGroups()->count(), 'has_audience_targeting' => !empty($this->target_audience), 'has_segment_targeting' => !empty($this->target_segments)];
    }
    /**
     * Handle getContentSummary functionality with proper error handling.
     * @return array
     */
    public function getContentSummary(): array
    {
        return ['has_subject' => !empty($this->subject), 'has_content' => !empty($this->content), 'has_cta' => !empty($this->cta_text) && !empty($this->cta_url), 'has_banner' => !empty($this->banner_image), 'content_length' => strlen(strip_tags($this->content ?? '')), 'subject_length' => strlen($this->subject ?? '')];
    }
    /**
     * Handle getAutomationSummary functionality with proper error handling.
     * @return array
     */
    public function getAutomationSummary(): array
    {
        return ['auto_start' => $this->auto_start, 'auto_end' => $this->auto_end, 'auto_pause_on_budget' => $this->auto_pause_on_budget, 'send_notifications' => $this->send_notifications, 'track_conversions' => $this->track_conversions, 'is_featured' => $this->is_featured, 'social_media_ready' => $this->social_media_ready];
    }
}