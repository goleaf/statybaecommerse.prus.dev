<?php

declare(strict_types=1);

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
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[ScopedBy([ActiveScope::class, StatusScope::class, ActiveCampaignScope::class])]
final /**
 * Campaign
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class Campaign extends Model
{
    use HasFactory, SoftDeletes;
    use HasTranslations;

    protected $table = 'discount_campaigns';

    protected $fillable = [
        'name',
        'slug',
        'starts_at',
        'ends_at',
        'channel_id',
        'zone_id',
        'status',
        'metadata',
        'is_featured',
        'send_notifications',
        'track_conversions',
        'max_uses',
        'budget_limit',
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

    /**
     * Get the campaign's latest view.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestView(): HasOne
    {
        return $this->views()->one()->ofMany('viewed_at', 'max');
    }

    /**
     * Get the campaign's latest click.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestClick(): HasOne
    {
        return $this->clicks()->one()->ofMany('clicked_at', 'max');
    }

    /**
     * Get the campaign's latest conversion.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestConversion(): HasOne
    {
        return $this->conversions()->one()->ofMany('converted_at', 'max');
    }

    /**
     * Get the campaign's latest schedule.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestSchedule(): HasOne
    {
        return $this->schedules()->one()->latestOfMany();
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

        $metadata = $this->metadata ?? [];
        $metadata['total_views'] = ($metadata['total_views'] ?? 0) + 1;
        $this->update(['metadata' => $metadata]);
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

        $metadata = $this->metadata ?? [];
        $metadata['total_clicks'] = ($metadata['total_clicks'] ?? 0) + 1;
        $this->update(['metadata' => $metadata]);
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

        $metadata = $this->metadata ?? [];
        $metadata['total_conversions'] = ($metadata['total_conversions'] ?? 0) + 1;
        $metadata['total_revenue'] = ($metadata['total_revenue'] ?? 0) + $conversionValue;
        $metadata['conversion_rate'] = $this->getConversionRate();
        $this->update(['metadata' => $metadata]);
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

    // Additional helper methods and accessors
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->slug;
    }

    // Accessors for metadata fields
    public function getDescriptionAttribute(): ?string
    {
        return $this->metadata['description'] ?? null;
    }

    public function getTypeAttribute(): ?string
    {
        return $this->metadata['type'] ?? 'banner';
    }

    public function getSubjectAttribute(): ?string
    {
        return $this->metadata['subject'] ?? null;
    }

    public function getContentAttribute(): ?string
    {
        return $this->metadata['content'] ?? null;
    }

    public function getStartDateAttribute(): ?\Carbon\Carbon
    {
        return $this->starts_at;
    }

    public function getEndDateAttribute(): ?\Carbon\Carbon
    {
        return $this->ends_at;
    }

    public function getBudgetAttribute(): ?float
    {
        return $this->metadata['budget'] ?? null;
    }

    public function getTotalViewsAttribute(): int
    {
        return $this->metadata['total_views'] ?? 0;
    }

    public function getTotalClicksAttribute(): int
    {
        return $this->metadata['total_clicks'] ?? 0;
    }

    public function getTotalConversionsAttribute(): int
    {
        return $this->metadata['total_conversions'] ?? 0;
    }

    public function getTotalRevenueAttribute(): float
    {
        return $this->metadata['total_revenue'] ?? 0;
    }

    public function getConversionRateAttribute(): float
    {
        return $this->metadata['conversion_rate'] ?? 0;
    }

    public function getTargetAudienceAttribute(): ?array
    {
        return $this->metadata['target_audience'] ?? null;
    }

    public function getTargetSegmentsAttribute(): ?array
    {
        return $this->metadata['target_segments'] ?? null;
    }

    public function getDisplayPriorityAttribute(): int
    {
        return $this->metadata['display_priority'] ?? 0;
    }

    public function getBannerImageAttribute(): ?string
    {
        return $this->metadata['banner_image'] ?? null;
    }

    public function getBannerAltTextAttribute(): ?string
    {
        return $this->metadata['banner_alt_text'] ?? null;
    }

    public function getCtaTextAttribute(): ?string
    {
        return $this->metadata['cta_text'] ?? null;
    }

    public function getCtaUrlAttribute(): ?string
    {
        return $this->metadata['cta_url'] ?? null;
    }

    public function getAutoStartAttribute(): bool
    {
        return $this->metadata['auto_start'] ?? false;
    }

    public function getAutoEndAttribute(): bool
    {
        return $this->metadata['auto_end'] ?? false;
    }

    public function getAutoPauseOnBudgetAttribute(): bool
    {
        return $this->metadata['auto_pause_on_budget'] ?? false;
    }

    public function getMetaTitleAttribute(): ?string
    {
        return $this->metadata['meta_title'] ?? null;
    }

    public function getMetaDescriptionAttribute(): ?string
    {
        return $this->metadata['meta_description'] ?? null;
    }

    public function getSocialMediaReadyAttribute(): bool
    {
        return $this->metadata['social_media_ready'] ?? false;
    }

    public function getFormattedDescriptionAttribute(): string
    {
        return $this->description ? strip_tags($this->description) : '';
    }

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

    public function getDurationAttribute(): ?int
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }

        return $this->start_date->diffInDays($this->end_date);
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        $remaining = now()->diffInDays($this->end_date, false);
        return $remaining > 0 ? $remaining : 0;
    }

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

        return min(100, max(0, ($elapsed / $total) * 100));
    }

    public function getBudgetUtilizationAttribute(): float
    {
        if (!$this->budget_limit || $this->budget_limit <= 0) {
            return 0;
        }

        return min(100, ($this->total_revenue / $this->budget_limit) * 100);
    }

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

    public function getStatistics(): array
    {
        return [
            'views' => $this->total_views,
            'clicks' => $this->total_clicks,
            'conversions' => $this->total_conversions,
            'revenue' => $this->total_revenue,
            'conversion_rate' => $this->getConversionRate(),
            'click_through_rate' => $this->getClickThroughRate(),
            'roi' => $this->getROI(),
            'performance_score' => $this->performance_score,
            'performance_grade' => $this->performance_grade,
            'performance_color' => $this->performance_color,
            'budget_utilization' => $this->budget_utilization,
            'progress_percentage' => $this->progress_percentage,
            'days_remaining' => $this->days_remaining,
            'duration' => $this->duration,
        ];
    }

    public function getFormattedBudgetAttribute(): string
    {
        return '€' . number_format($this->budget, 2);
    }

    public function getFormattedBudgetLimitAttribute(): string
    {
        return '€' . number_format($this->budget_limit, 2);
    }

    public function getFormattedTotalRevenueAttribute(): string
    {
        return '€' . number_format($this->total_revenue, 2);
    }

    public function getFormattedROIAttribute(): string
    {
        return number_format($this->getROI(), 2) . '%';
    }

    public function getFormattedConversionRateAttribute(): string
    {
        return number_format($this->getConversionRate(), 2) . '%';
    }

    public function getFormattedClickThroughRateAttribute(): string
    {
        return number_format($this->getClickThroughRate(), 2) . '%';
    }

    public function getFormattedBudgetUtilizationAttribute(): string
    {
        return number_format($this->budget_utilization, 2) . '%';
    }

    public function getFormattedProgressPercentageAttribute(): string
    {
        return number_format($this->progress_percentage, 1) . '%';
    }

    public function isHighPerforming(): bool
    {
        return $this->performance_score >= 80;
    }

    public function isUnderperforming(): bool
    {
        return $this->performance_score < 40;
    }

    public function needsAttention(): bool
    {
        return $this->isUnderperforming() || $this->budget_utilization > 90;
    }

    public function canBeActivated(): bool
    {
        return $this->status === 'draft' || $this->status === 'scheduled';
    }

    public function canBePaused(): bool
    {
        return $this->status === 'active';
    }

    public function canBeResumed(): bool
    {
        return $this->status === 'paused';
    }

    public function canBeCompleted(): bool
    {
        return $this->status === 'active' && $this->isExpired();
    }

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

    public function getTargetingSummary(): array
    {
        return [
            'categories_count' => $this->targetCategories()->count(),
            'products_count' => $this->targetProducts()->count(),
            'customer_groups_count' => $this->targetCustomerGroups()->count(),
            'has_audience_targeting' => !empty($this->target_audience),
            'has_segment_targeting' => !empty($this->target_segments),
        ];
    }

    public function getContentSummary(): array
    {
        return [
            'has_subject' => !empty($this->subject),
            'has_content' => !empty($this->content),
            'has_cta' => !empty($this->cta_text) && !empty($this->cta_url),
            'has_banner' => !empty($this->banner_image),
            'content_length' => strlen(strip_tags($this->content ?? '')),
            'subject_length' => strlen($this->subject ?? ''),
        ];
    }

    public function getAutomationSummary(): array
    {
        return [
            'auto_start' => $this->auto_start,
            'auto_end' => $this->auto_end,
            'auto_pause_on_budget' => $this->auto_pause_on_budget,
            'send_notifications' => $this->send_notifications,
            'track_conversions' => $this->track_conversions,
            'is_featured' => $this->is_featured,
            'social_media_ready' => $this->social_media_ready,
        ];
    }
}
