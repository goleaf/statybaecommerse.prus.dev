<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class Campaign extends Model
{
    use HasFactory, SoftDeletes;
    use HasTranslations;

    protected $table = 'discount_campaigns';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'subject',
        'content',
        'starts_at',
        'ends_at',
        'start_date',
        'end_date',
        'budget',
        'channel_id',
        'zone_id',
        'status',
        'metadata',
        'is_featured',
        'send_notifications',
        'track_conversions',
        'max_uses',
        'budget_limit',
        'total_views',
        'total_clicks',
        'total_conversions',
        'total_revenue',
        'conversion_rate',
        'target_audience',
        'target_categories',
        'target_products',
        'target_customer_groups',
        'target_segments',
        'display_priority',
        'banner_image',
        'banner_alt_text',
        'cta_text',
        'cta_url',
        'auto_start',
        'auto_end',
        'auto_pause_on_budget',
        'meta_title',
        'meta_description',
        'social_media_ready',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'budget' => 'decimal:2',
            'metadata' => 'array',
            'is_featured' => 'boolean',
            'send_notifications' => 'boolean',
            'track_conversions' => 'boolean',
            'max_uses' => 'integer',
            'budget_limit' => 'decimal:2',
            'total_views' => 'integer',
            'total_clicks' => 'integer',
            'total_conversions' => 'integer',
            'total_revenue' => 'decimal:2',
            'conversion_rate' => 'decimal:2',
            'target_audience' => 'array',
            'target_categories' => 'array',
            'target_products' => 'array',
            'target_customer_groups' => 'array',
            'target_segments' => 'array',
            'display_priority' => 'integer',
            'auto_start' => 'boolean',
            'auto_end' => 'boolean',
            'auto_pause_on_budget' => 'boolean',
            'social_media_ready' => 'boolean',
        ];
    }

    protected string $translationModel = \App\Models\Translations\CampaignTranslation::class;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'campaign_discount');
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(CampaignView::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(CampaignClick::class);
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(CampaignConversion::class);
    }

    public function customerSegments(): HasMany
    {
        return $this->hasMany(CampaignCustomerSegment::class);
    }

    public function productTargets(): HasMany
    {
        return $this->hasMany(CampaignProductTarget::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(CampaignSchedule::class);
    }

    public function orders(): HasManyThrough
    {
        return $this->hasManyThrough(Order::class, CampaignConversion::class);
    }

    public function targetCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'campaign_categories');
    }

    public function targetProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'campaign_products');
    }

    public function targetCustomerGroups(): BelongsToMany
    {
        return $this->belongsToMany(CustomerGroup::class, 'campaign_customer_groups');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', 'active')
            ->where(function ($q) {
                $q
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query
            ->where('status', 'expired')
            ->orWhere(function ($q) {
                $q
                    ->whereNotNull('ends_at')
                    ->where('ends_at', '<', now());
            });
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeByPriority(Builder $query): Builder
    {
        return $query->orderBy('display_priority', 'desc');
    }

    public function scopeForChannel(Builder $query, int $channelId): Builder
    {
        return $query->where('channel_id', $channelId);
    }

    public function scopeForZone(Builder $query, int $zoneId): Builder
    {
        return $query->where('zone_id', $zoneId);
    }

    public function scopeWithAnalytics(Builder $query): Builder
    {
        return $query->where('track_conversions', true);
    }

    public function scopeSocialMediaReady(Builder $query): Builder
    {
        return $query->where('social_media_ready', true);
    }

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

    public function isExpired(): bool
    {
        return $this->ends_at && $this->ends_at->lt(now());
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled' && $this->starts_at && $this->starts_at->gt(now());
    }

    public function isWithinBudget(): bool
    {
        if (! $this->budget_limit) {
            return true;
        }

        return $this->total_revenue < $this->budget_limit;
    }

    public function getClickThroughRate(): float
    {
        if ($this->total_views === 0) {
            return 0;
        }

        return round(($this->total_clicks / $this->total_views) * 100, 2);
    }

    public function getConversionRate(): float
    {
        if ($this->total_clicks === 0) {
            return 0;
        }

        return round(($this->total_conversions / $this->total_clicks) * 100, 2);
    }

    public function getROI(): float
    {
        if ($this->budget_limit === 0) {
            return 0;
        }

        return round((($this->total_revenue - $this->budget_limit) / $this->budget_limit) * 100, 2);
    }

    public function recordView(?string $sessionId = null, ?string $ipAddress = null, ?string $userAgent = null, ?string $referer = null, ?int $customerId = null): void
    {
        $this->views()->create([
            'session_id' => $sessionId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'referer' => $referer,
            'customer_id' => $customerId,
            'viewed_at' => now(),
        ]);

        $this->increment('total_views');
    }

    public function recordClick(string $clickType = 'cta', ?string $clickedUrl = null, ?string $sessionId = null, ?string $ipAddress = null, ?string $userAgent = null, ?int $customerId = null): void
    {
        $this->clicks()->create([
            'session_id' => $sessionId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'click_type' => $clickType,
            'clicked_url' => $clickedUrl,
            'customer_id' => $customerId,
            'clicked_at' => now(),
        ]);

        $this->increment('total_clicks');
    }

    public function recordConversion(string $conversionType = 'purchase', float $conversionValue = 0, ?int $orderId = null, ?int $customerId = null, ?string $sessionId = null, array $conversionData = []): void
    {
        $this->conversions()->create([
            'order_id' => $orderId,
            'customer_id' => $customerId,
            'conversion_type' => $conversionType,
            'conversion_value' => $conversionValue,
            'session_id' => $sessionId,
            'conversion_data' => $conversionData,
            'converted_at' => now(),
        ]);

        $this->increment('total_conversions');
        $this->increment('total_revenue', $conversionValue);
        $this->update(['conversion_rate' => $this->getConversionRate()]);
    }

    public function getBannerUrl(): ?string
    {
        if (! $this->banner_image) {
            return null;
        }

        return asset('storage/campaigns/'.$this->banner_image);
    }

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

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->singleFile();

        $this
            ->addMediaCollection('banners')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->singleFile();

        $this
            ->addMediaCollection('attachments')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->performOnCollections('images', 'banners');

        $this
            ->addMediaConversion('medium')
            ->width(400)
            ->height(300)
            ->sharpen(10)
            ->performOnCollections('images', 'banners');

        $this
            ->addMediaConversion('large')
            ->width(800)
            ->height(600)
            ->sharpen(10)
            ->performOnCollections('images', 'banners');
    }

    public function getImageUrl(string $conversion = ''): ?string
    {
        $media = $this->getFirstMedia('images');

        return $media ? $media->getUrl($conversion) : null;
    }
}
