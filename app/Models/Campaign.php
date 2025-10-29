<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveCampaignScope;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\StatusScope;
use App\Support\Storage\SecureStorage;
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
 * @property mixed  $table
 * @property mixed  $fillable
 * @property string $translationModel
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, StatusScope::class, ActiveCampaignScope::class])]
final class Campaign extends Model
{
    use HasFactory;
    use HasTranslations;
    use OrdersByName {
        scopeOrderedByName as scopeOrderedByNameBase;
    }
    use SoftDeletes;

    protected $table = 'discount_campaigns';

    protected $fillable = ['name', 'slug', 'starts_at', 'ends_at', 'channel_id', 'status', 'is_active', 'metadata', 'is_featured', 'send_notifications', 'track_conversions', 'max_uses', 'budget_limit'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'start_date' => 'datetime', 'end_date' => 'datetime', 'budget' => 'decimal:2', 'metadata' => 'array', 'is_active' => 'boolean', 'is_featured' => 'boolean', 'send_notifications' => 'boolean', 'track_conversions' => 'boolean', 'max_uses' => 'integer', 'budget_limit' => 'decimal:2', 'total_views' => 'integer', 'total_clicks' => 'integer', 'total_conversions' => 'integer', 'total_revenue' => 'decimal:2', 'conversion_rate' => 'decimal:2', 'target_audience' => 'array', 'target_categories' => 'array', 'target_products' => 'array', 'target_customer_groups' => 'array', 'target_segments' => 'array', 'display_priority' => 'integer', 'auto_start' => 'boolean', 'auto_end' => 'boolean', 'auto_pause_on_budget' => 'boolean', 'social_media_ready' => 'boolean'];
    }

    protected string $translationModel = \App\Models\Translations\CampaignTranslation::class;

    /**
     * Handle getRouteKeyName functionality with proper error handling.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Handle getTranslatedName functionality with proper error handling.
     */
    public function getTranslatedName(?string $locale = null): string
    {
        if ($locale === null) {
            return $this->name;
        }
        // Load translations if not already loaded
        if (! $this->relationLoaded('translations')) {
            $this->load('translations');
        }
        $translation = $this->translations->firstWhere('locale', $locale);
        if ($translation && ! empty($translation->name)) {
            return $translation->name;
        }

        // If no translation found for the specific locale, return the model's name
        return $this->name;
    }

    /**
     * Handle discounts functionality with proper error handling.
     */
    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'campaign_discount');
    }

    /**
     * Provide a stable alphabetical ordering scope with a deterministic fallback.
     */
    public function scopeOrderedByName(Builder $query, string $direction = 'asc'): Builder
    {
        // Normalise the requested direction so callers cannot smuggle arbitrary SQL fragments.
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        $qualifiedColumn = $query->qualifyColumn('name');

        // Apply a case-insensitive sort to keep behaviour consistent across SQLite and MySQL.
        $query->orderByRaw(sprintf('LOWER(%s) %s', $qualifiedColumn, $direction));

        // Delegate to the shared trait implementation so we retain the defensive column handling.
        return $this->scopeOrderedByNameBase($query, $direction);
    }

    /**
     * Handle channel functionality with proper error handling.
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Handle views functionality with proper error handling.
     */
    public function views(): HasMany
    {
        return $this->hasMany(CampaignView::class);
    }

    /**
     * Handle clicks functionality with proper error handling.
     */
    public function clicks(): HasMany
    {
        return $this->hasMany(CampaignClick::class);
    }

    /**
     * Handle conversions functionality with proper error handling.
     */
    public function conversions(): HasMany
    {
        return $this->hasMany(CampaignConversion::class);
    }

    /**
     * Handle customerSegments functionality with proper error handling.
     */
    public function customerSegments(): HasMany
    {
        return $this->hasMany(CampaignCustomerSegment::class);
    }

    /**
     * Handle productTargets functionality with proper error handling.
     */
    public function productTargets(): HasMany
    {
        return $this->hasMany(CampaignProductTarget::class);
    }

    /**
     * Handle schedules functionality with proper error handling.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(CampaignSchedule::class);
    }

    /**
     * Handle latestView functionality with proper error handling.
     */
    public function latestView(): HasOne
    {
        return $this->views()->one()->ofMany('viewed_at', 'max');
    }

    /**
     * Handle latestClick functionality with proper error handling.
     */
    public function latestClick(): HasOne
    {
        return $this->clicks()->one()->ofMany('clicked_at', 'max');
    }

    /**
     * Handle latestConversion functionality with proper error handling.
     */
    public function latestConversion(): HasOne
    {
        return $this->conversions()->one()->ofMany('converted_at', 'max');
    }

    /**
     * Handle highestValueConversion functionality with proper error handling.
     */
    public function highestValueConversion(): HasOne
    {
        return $this->conversions()->one()->ofMany('conversion_value', 'max');
    }

    /**
     * Handle lowestValueConversion functionality with proper error handling.
     */
    public function lowestValueConversion(): HasOne
    {
        return $this->conversions()->one()->ofMany('conversion_value', 'min');
    }

    /**
     * Handle latestSchedule functionality with proper error handling.
     */
    public function latestSchedule(): HasOne
    {
        return $this->schedules()->one()->latestOfMany();
    }

    /**
     * Handle orders functionality with proper error handling.
     */
    public function orders(): HasManyThrough
    {
        return $this->hasManyThrough(Order::class, CampaignConversion::class);
    }

    /**
     * Handle latestOrder functionality with proper error handling.
     */
    public function latestOrder(): HasOneThrough
    {
        return $this->orders()->one()->latestOfMany();
    }

    /**
     * Handle targetCategories functionality with proper error handling.
     */
    public function targetCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'campaign_categories');
    }

    /**
     * Handle targetProducts functionality with proper error handling.
     */
    public function targetProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'campaign_products');
    }

    /**
     * Handle targetCustomerGroups functionality with proper error handling.
     */
    public function targetCustomerGroups(): BelongsToMany
    {
        return $this->belongsToMany(CustomerGroup::class, 'campaign_customer_groups');
    }

    /**
     * Handle scopeActive functionality with proper error handling.
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
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Handle scopeExpired functionality with proper error handling.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired')->orWhere(function ($q) {
            $q->whereNotNull('ends_at')->where('ends_at', '<', now());
        });
    }

    /**
     * Handle scopeFeatured functionality with proper error handling.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Handle scopeByPriority functionality with proper error handling.
     */
    public function scopeByPriority(Builder $query): Builder
    {
        return $query->orderBy('display_priority', 'desc');
    }

    /**
     * Handle scopeForChannel functionality with proper error handling.
     */
    public function scopeForChannel(Builder $query, int $channelId): Builder
    {
        return $query->where('channel_id', $channelId);
    }

    /**
     * Handle scopeWithAnalytics functionality with proper error handling.
     */
    public function scopeWithAnalytics(Builder $query): Builder
    {
        return $query->where('track_conversions', true);
    }

    /**
     * Handle scopeSocialMediaReady functionality with proper error handling.
     */
    public function scopeSocialMediaReady(Builder $query): Builder
    {
        return $query->where('social_media_ready', true);
    }

    /**
     * Handle isActive functionality with proper error handling.
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
     */
    public function isExpired(): bool
    {
        return $this->ends_at && $this->ends_at->lt(now());
    }

    /**
     * Handle isScheduled functionality with proper error handling.
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled' && $this->starts_at && $this->starts_at->gt(now());
    }

    /**
     * Handle isInactive functionality with proper error handling.
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive' || ! $this->isActive();
    }

    /**
     * Handle isUpcoming functionality with proper error handling.
     */
    public function isUpcoming(): bool
    {
        return $this->starts_at && $this->starts_at->gt(now());
    }

    /**
     * Handle isWithinBudget functionality with proper error handling.
     */
    public function isWithinBudget(): bool
    {
        if (! $this->budget_limit) {
            return true;
        }

        return $this->total_revenue < $this->budget_limit;
    }

    /**
     * Handle getClickThroughRate functionality with proper error handling.
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
     */
    public function recordView(?string $sessionId = null, ?string $ipAddress = null, ?string $userAgent = null, ?string $referer = null, ?int $customerId = null): void
    {
        // Persist the raw view event so downstream analytics can inspect the interaction payload.
        $this->views()->create([
            'session_id'  => $sessionId,
            'ip_address'  => $ipAddress,
            'user_agent'  => $userAgent,
            'referer'     => $referer,
            'customer_id' => $customerId,
            'viewed_at'   => now(),
        ]);

        // Keep the denormalised counter column in sync for aggregate queries without touching timestamps.
        self::withoutTimestamps(function (): void {
            $this->increment('total_views');
        });

        // Mirror the refreshed totals in metadata for legacy widgets still reading from JSON blobs.
        $this->persistAnalyticsState(['total_views']);
    }

    /**
     * Handle recordClick functionality with proper error handling.
     */
    public function recordClick(string $clickType = 'cta', ?string $clickedUrl = null, ?string $sessionId = null, ?string $ipAddress = null, ?string $userAgent = null, ?int $customerId = null): void
    {
        // Capture the click record before mutating aggregates to preserve an auditable event log.
        $this->clicks()->create([
            'session_id'  => $sessionId,
            'ip_address'  => $ipAddress,
            'user_agent'  => $userAgent,
            'click_type'  => $clickType,
            'clicked_url' => $clickedUrl,
            'customer_id' => $customerId,
            'clicked_at'  => now(),
        ]);

        // Increment the counter column atomically without bumping timestamps for noise-free analytics.
        self::withoutTimestamps(function (): void {
            $this->increment('total_clicks');
        });

        // Keep legacy metadata snapshots aligned with the authoritative database column.
        $this->persistAnalyticsState(['total_clicks']);
    }

    /**
     * Handle recordConversion functionality with proper error handling.
     */
    public function recordConversion(string $conversionType = 'purchase', float $conversionValue = 0, ?int $orderId = null, ?int $customerId = null, ?string $sessionId = null, array $conversionData = []): void
    {
        // Store the conversion snapshot including attribution metadata for full revenue traceability.
        $this->conversions()->create([
            'order_id'         => $orderId,
            'customer_id'      => $customerId,
            'conversion_type'  => $conversionType,
            'conversion_value' => $conversionValue,
            'session_id'       => $sessionId,
            'conversion_data'  => $conversionData,
            'converted_at'     => now(),
        ]);

        // Update conversion and revenue aggregates without polluting updated_at.
        self::withoutTimestamps(function () use ($conversionValue): void {
            $this->increment('total_conversions');
            $this->increment('total_revenue', $conversionValue);
        });

        // Recompute derived analytics and keep metadata mirrors consistent for backwards compatibility.
        $this->persistAnalyticsState(['total_conversions', 'total_revenue'], true);
    }

    /**
     * Synchronise denormalised analytics columns with the legacy metadata payload.
     */
    private function persistAnalyticsState(array $columns, bool $recalculateConversionRate = false): void
    {
        // Guarantee the metadata array exists even for legacy rows created before JSON support.
        $metadata = $this->metadataPayload();

        // Optionally refresh the stored conversion rate before persisting the snapshot.
        if ($recalculateConversionRate) {
            $this->conversion_rate = $this->getConversionRate();
        }

        // Mirror the latest column values into metadata to support consumers that still rely on it.
        foreach ($columns as $column) {
            $metadata[$column] = $this->getAttribute($column);
        }

        if ($recalculateConversionRate) {
            $metadata['conversion_rate'] = $this->conversion_rate;
        }

        // Persist quietly to avoid firing events while still updating the authoritative attributes.
        $attributesToPersist = ['metadata' => $metadata];

        if ($recalculateConversionRate) {
            $attributesToPersist['conversion_rate'] = $this->conversion_rate;
        }

        $this->forceFill($attributesToPersist)->saveQuietly();
    }

    /**
     * Provide a normalised metadata array for accessors that still support legacy payloads.
     *
     * @return array<string, mixed>
     */
    private function metadataPayload(): array
    {
        // Cast null metadata to an empty array so array access stays safe across PHP versions.
        return is_array($this->metadata) ? $this->metadata : [];
    }

    /**
     * Handle getBannerUrl functionality with proper error handling.
     */
    public function getBannerUrl(): ?string
    {
        if (! $this->banner_image) {
            return null;
        }

        $path = 'campaigns/' . ltrim((string) $this->banner_image, '/');

        return SecureStorage::temporarySignedUrl($path);
    }

    /**
     * Handle getStatusBadgeColor functionality with proper error handling.
     */
    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'active'    => 'success',
            'scheduled' => 'warning',
            'paused'    => 'secondary',
            'expired'   => 'danger',
            'draft'     => 'info',
            default     => 'secondary',
        };
    }

    /**
     * Handle getStatusLabel functionality with proper error handling.
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'active'    => __('campaigns.status.active'),
            'scheduled' => __('campaigns.status.scheduled'),
            'paused'    => __('campaigns.status.paused'),
            'expired'   => __('campaigns.status.expired'),
            'draft'     => __('campaigns.status.draft'),
            default     => __('campaigns.status.unknown'),
        };
    }

    /**
     * Handle registerMediaCollections functionality with proper error handling.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])->singleFile();
        $this->addMediaCollection('banners')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])->singleFile();
        $this->addMediaCollection('attachments')->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Handle registerMediaConversions functionality with proper error handling.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(150)->height(150)->sharpen(10)->performOnCollections('images', 'banners');
        $this->addMediaConversion('medium')->width(400)->height(300)->sharpen(10)->performOnCollections('images', 'banners');
        $this->addMediaConversion('large')->width(800)->height(600)->sharpen(10)->performOnCollections('images', 'banners');
    }

    /**
     * Handle getImageUrl functionality with proper error handling.
     */
    public function getImageUrl(string $conversion = ''): ?string
    {
        $media = $this->getFirstMedia('images');

        return $media ? $media->getUrl($conversion) : null;
    }

    // Additional helper methods and accessors

    /**
     * Handle getDisplayNameAttribute functionality with proper error handling.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->slug;
    }

    // Accessors for metadata fields

    /**
     * Handle getDescriptionAttribute functionality with proper error handling.
     */
    public function getDescriptionAttribute(?string $value): ?string
    {
        // Use the persisted column value when present, falling back to metadata for legacy records.
        if ($value !== null) {
            return $value;
        }

        return $this->metadataPayload()['description'] ?? null;
    }

    /**
     * Handle getTypeAttribute functionality with proper error handling.
     */
    public function getTypeAttribute(?string $value): ?string
    {
        // Honour the stored campaign type column but retain backward compatibility with metadata snapshots.
        if ($value !== null) {
            return $value;
        }

        return $this->metadataPayload()['type'] ?? 'banner';
    }

    /**
     * Handle getSubjectAttribute functionality with proper error handling.
     */
    public function getSubjectAttribute(?string $value): ?string
    {
        // Prioritise the dedicated subject column and fall back to the legacy metadata payload otherwise.
        if ($value !== null) {
            return $value;
        }

        return $this->metadataPayload()['subject'] ?? null;
    }

    /**
     * Handle getContentAttribute functionality with proper error handling.
     */
    public function getContentAttribute(?string $value): ?string
    {
        // Prefer the column content when hydrated while still supporting historical metadata dumps.
        if ($value !== null) {
            return $value;
        }

        return $this->metadataPayload()['content'] ?? null;
    }

    /**
     * Handle getStartDateAttribute functionality with proper error handling.
     *
     * @return Carbon\Carbon|null
     */
    public function getStartDateAttribute(): ?\Carbon\Carbon
    {
        return $this->starts_at;
    }

    /**
     * Handle getEndDateAttribute functionality with proper error handling.
     *
     * @return Carbon\Carbon|null
     */
    public function getEndDateAttribute(): ?\Carbon\Carbon
    {
        return $this->ends_at;
    }

    /**
     * Handle getBudgetAttribute functionality with proper error handling.
     */
    public function getBudgetAttribute(?float $value): ?float
    {
        // Retain metadata support for legacy rows while allowing column overrides when introduced.
        if ($value !== null) {
            return $value;
        }

        $metadata = $this->metadataPayload();

        return isset($metadata['budget']) ? (float) $metadata['budget'] : null;
    }

    /**
     * Handle getTotalViewsAttribute functionality with proper error handling.
     */
    public function getTotalViewsAttribute(?int $value): int
    {
        // Aggregate counters should prefer the authoritative column yet still honour legacy metadata.
        if ($value !== null) {
            return $value;
        }

        return (int) ($this->metadataPayload()['total_views'] ?? 0);
    }

    /**
     * Handle getTotalClicksAttribute functionality with proper error handling.
     */
    public function getTotalClicksAttribute(?int $value): int
    {
        // Favour the denormalised column while keeping backwards compatibility with JSON payloads.
        if ($value !== null) {
            return $value;
        }

        return (int) ($this->metadataPayload()['total_clicks'] ?? 0);
    }

    /**
     * Handle getTotalConversionsAttribute functionality with proper error handling.
     */
    public function getTotalConversionsAttribute(?int $value): int
    {
        // Ensure reporting reads from the persisted column before falling back to historical metadata.
        if ($value !== null) {
            return $value;
        }

        return (int) ($this->metadataPayload()['total_conversions'] ?? 0);
    }

    /**
     * Handle getTotalRevenueAttribute functionality with proper error handling.
     */
    public function getTotalRevenueAttribute(?float $value): float
    {
        // Prefer the up-to-date revenue column while accepting metadata for migrated records.
        if ($value !== null) {
            return $value;
        }

        return (float) ($this->metadataPayload()['total_revenue'] ?? 0);
    }

    /**
     * Handle getConversionRateAttribute functionality with proper error handling.
     */
    public function getConversionRateAttribute(?float $value): float
    {
        // Trust the stored conversion rate when calculated, otherwise fall back to the legacy JSON snapshot.
        if ($value !== null) {
            return $value;
        }

        return (float) ($this->metadataPayload()['conversion_rate'] ?? 0);
    }

    /**
     * Handle getTargetAudienceAttribute functionality with proper error handling.
     */
    public function getTargetAudienceAttribute(?array $value): ?array
    {
        // Return the native JSON column when available while preserving compatibility with metadata dumps.
        if ($value !== null) {
            return $value;
        }

        $metadata = $this->metadataPayload();

        return isset($metadata['target_audience']) ? (array) $metadata['target_audience'] : null;
    }

    /**
     * Handle getTargetSegmentsAttribute functionality with proper error handling.
     */
    public function getTargetSegmentsAttribute(?array $value): ?array
    {
        // Surface the stored column when defined, otherwise rely on the metadata fallback.
        if ($value !== null) {
            return $value;
        }

        $metadata = $this->metadataPayload();

        return isset($metadata['target_segments']) ? (array) $metadata['target_segments'] : null;
    }

    /**
     * Handle getDisplayPriorityAttribute functionality with proper error handling.
     */
    public function getDisplayPriorityAttribute(?int $value): int
    {
        // Read from the dedicated priority column when set, otherwise fall back to metadata defaults.
        if ($value !== null) {
            return $value;
        }

        return (int) ($this->metadataPayload()['display_priority'] ?? 0);
    }

    /**
     * Handle getBannerImageAttribute functionality with proper error handling.
     */
    public function getBannerImageAttribute(?string $value): ?string
    {
        // Prefer the stored banner image column while keeping legacy metadata compatible.
        if ($value !== null) {
            return $value;
        }

        return $this->metadataPayload()['banner_image'] ?? null;
    }

    /**
     * Handle getBannerAltTextAttribute functionality with proper error handling.
     */
    public function getBannerAltTextAttribute(?string $value): ?string
    {
        // Ensure the accessibility text uses the column when filled, with metadata as a safety net.
        if ($value !== null) {
            return $value;
        }

        return $this->metadataPayload()['banner_alt_text'] ?? null;
    }

    /**
     * Handle getCtaTextAttribute functionality with proper error handling.
     */
    public function getCtaTextAttribute(?string $value): ?string
    {
        // Return the column-backed CTA copy when available before consulting metadata.
        if ($value !== null) {
            return $value;
        }

        return $this->metadataPayload()['cta_text'] ?? null;
    }

    /**
     * Handle getCtaUrlAttribute functionality with proper error handling.
     */
    public function getCtaUrlAttribute(?string $value): ?string
    {
        // Prefer the stored URL column and fall back to metadata when migrating older records.
        if ($value !== null) {
            return $value;
        }

        return $this->metadataPayload()['cta_url'] ?? null;
    }

    /**
     * Handle getAutoStartAttribute functionality with proper error handling.
     */
    public function getAutoStartAttribute(?bool $value): bool
    {
        // Automation toggles should respect the persisted boolean column while preserving metadata support.
        if ($value !== null) {
            return (bool) $value;
        }

        return (bool) ($this->metadataPayload()['auto_start'] ?? false);
    }

    /**
     * Handle getAutoEndAttribute functionality with proper error handling.
     */
    public function getAutoEndAttribute(?bool $value): bool
    {
        // Honour the stored column before falling back to metadata when dealing with legacy exports.
        if ($value !== null) {
            return (bool) $value;
        }

        return (bool) ($this->metadataPayload()['auto_end'] ?? false);
    }

    /**
     * Handle getAutoPauseOnBudgetAttribute functionality with proper error handling.
     */
    public function getAutoPauseOnBudgetAttribute(?bool $value): bool
    {
        // Always favour the dedicated column value and rely on metadata only for backwards compatibility.
        if ($value !== null) {
            return (bool) $value;
        }

        return (bool) ($this->metadataPayload()['auto_pause_on_budget'] ?? false);
    }

    /**
     * Handle getMetaTitleAttribute functionality with proper error handling.
     */
    public function getMetaTitleAttribute(?string $value): ?string
    {
        // Prefer the SEO title column with metadata available as a migration fallback.
        if ($value !== null) {
            return $value;
        }

        return $this->metadataPayload()['meta_title'] ?? null;
    }

    /**
     * Handle getMetaDescriptionAttribute functionality with proper error handling.
     */
    public function getMetaDescriptionAttribute(?string $value): ?string
    {
        // Return the canonical SEO description column when set, otherwise consult metadata.
        if ($value !== null) {
            return $value;
        }

        return $this->metadataPayload()['meta_description'] ?? null;
    }

    /**
     * Handle getSocialMediaReadyAttribute functionality with proper error handling.
     */
    public function getSocialMediaReadyAttribute(?bool $value): bool
    {
        // Trust the stored boolean column for social readiness, keeping metadata for historic entries.
        if ($value !== null) {
            return (bool) $value;
        }

        return (bool) ($this->metadataPayload()['social_media_ready'] ?? false);
    }

    /**
     * Handle getFormattedDescriptionAttribute functionality with proper error handling.
     */
    public function getFormattedDescriptionAttribute(): string
    {
        return $this->description ? strip_tags($this->description) : '';
    }

    /**
     * Handle getTypeIconAttribute functionality with proper error handling.
     */
    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'email'  => 'heroicon-o-envelope',
            'sms'    => 'heroicon-o-device-phone-mobile',
            'push'   => 'heroicon-o-bell',
            'banner' => 'heroicon-o-photo',
            'popup'  => 'heroicon-o-window',
            'social' => 'heroicon-o-share',
            default  => 'heroicon-o-megaphone',
        };
    }

    /**
     * Handle getTypeColorAttribute functionality with proper error handling.
     */
    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'email'  => 'blue',
            'sms'    => 'green',
            'push'   => 'yellow',
            'banner' => 'purple',
            'popup'  => 'pink',
            'social' => 'red',
            default  => 'gray',
        };
    }

    /**
     * Handle getTypeLabelAttribute functionality with proper error handling.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'email'  => __('campaigns.types.email'),
            'sms'    => __('campaigns.types.sms'),
            'push'   => __('campaigns.types.push'),
            'banner' => __('campaigns.types.banner'),
            'popup'  => __('campaigns.types.popup'),
            'social' => __('campaigns.types.social'),
            default  => ucfirst($this->type),
        };
    }

    /**
     * Handle getDurationAttribute functionality with proper error handling.
     */
    public function getDurationAttribute(): ?int
    {
        if (! $this->start_date || ! $this->end_date) {
            return null;
        }

        return $this->start_date->diffInDays($this->end_date);
    }

    /**
     * Handle getDaysRemainingAttribute functionality with proper error handling.
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (! $this->end_date) {
            return null;
        }
        $remaining = now()->diffInDays($this->end_date, false);

        return $remaining > 0 ? $remaining : 0;
    }

    /**
     * Handle getProgressPercentageAttribute functionality with proper error handling.
     */
    public function getProgressPercentageAttribute(): float
    {
        if (! $this->start_date || ! $this->end_date) {
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
     */
    public function getBudgetUtilizationAttribute(): float
    {
        if (! $this->budget_limit || $this->budget_limit <= 0) {
            return 0;
        }

        return min(100, $this->total_revenue / $this->budget_limit * 100);
    }

    /**
     * Handle getPerformanceScoreAttribute functionality with proper error handling.
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
            default      => 'F',
        };
    }

    /**
     * Handle getPerformanceColorAttribute functionality with proper error handling.
     */
    public function getPerformanceColorAttribute(): string
    {
        $score = $this->performance_score;

        return match (true) {
            $score >= 80 => 'success',
            $score >= 60 => 'warning',
            $score >= 40 => 'info',
            default      => 'danger',
        };
    }

    /**
     * Handle getStatistics functionality with proper error handling.
     */
    public function getStatistics(): array
    {
        return ['views' => $this->total_views, 'clicks' => $this->total_clicks, 'conversions' => $this->total_conversions, 'revenue' => $this->total_revenue, 'conversion_rate' => $this->getConversionRate(), 'click_through_rate' => $this->getClickThroughRate(), 'roi' => $this->getROI(), 'performance_score' => $this->performance_score, 'performance_grade' => $this->performance_grade, 'performance_color' => $this->performance_color, 'budget_utilization' => $this->budget_utilization, 'progress_percentage' => $this->progress_percentage, 'days_remaining' => $this->days_remaining, 'duration' => $this->duration];
    }

    /**
     * Handle getFormattedBudgetAttribute functionality with proper error handling.
     */
    public function getFormattedBudgetAttribute(): string
    {
        return '€' . number_format($this->budget, 2);
    }

    /**
     * Handle getFormattedBudgetLimitAttribute functionality with proper error handling.
     */
    public function getFormattedBudgetLimitAttribute(): string
    {
        return '€' . number_format($this->budget_limit, 2);
    }

    /**
     * Handle getFormattedTotalRevenueAttribute functionality with proper error handling.
     */
    public function getFormattedTotalRevenueAttribute(): string
    {
        return '€' . number_format($this->total_revenue, 2);
    }

    /**
     * Handle getFormattedROIAttribute functionality with proper error handling.
     */
    public function getFormattedROIAttribute(): string
    {
        return number_format($this->getROI(), 2) . '%';
    }

    /**
     * Handle getFormattedConversionRateAttribute functionality with proper error handling.
     */
    public function getFormattedConversionRateAttribute(): string
    {
        return number_format($this->getConversionRate(), 2) . '%';
    }

    /**
     * Handle getFormattedClickThroughRateAttribute functionality with proper error handling.
     */
    public function getFormattedClickThroughRateAttribute(): string
    {
        return number_format($this->getClickThroughRate(), 2) . '%';
    }

    /**
     * Handle getFormattedBudgetUtilizationAttribute functionality with proper error handling.
     */
    public function getFormattedBudgetUtilizationAttribute(): string
    {
        return number_format($this->budget_utilization, 2) . '%';
    }

    /**
     * Handle getFormattedProgressPercentageAttribute functionality with proper error handling.
     */
    public function getFormattedProgressPercentageAttribute(): string
    {
        return number_format($this->progress_percentage, 1) . '%';
    }

    /**
     * Handle isHighPerforming functionality with proper error handling.
     */
    public function isHighPerforming(): bool
    {
        return $this->performance_score >= 80;
    }

    /**
     * Handle isUnderperforming functionality with proper error handling.
     */
    public function isUnderperforming(): bool
    {
        return $this->performance_score < 40;
    }

    /**
     * Handle needsAttention functionality with proper error handling.
     */
    public function needsAttention(): bool
    {
        return $this->isUnderperforming() || $this->budget_utilization > 90;
    }

    /**
     * Handle canBeActivated functionality with proper error handling.
     */
    public function canBeActivated(): bool
    {
        return $this->status === 'draft' || $this->status === 'scheduled';
    }

    /**
     * Handle canBePaused functionality with proper error handling.
     */
    public function canBePaused(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Handle canBeResumed functionality with proper error handling.
     */
    public function canBeResumed(): bool
    {
        return $this->status === 'paused';
    }

    /**
     * Handle canBeCompleted functionality with proper error handling.
     */
    public function canBeCompleted(): bool
    {
        return $this->status === 'active' && $this->isExpired();
    }

    /**
     * Handle getRecommendedActions functionality with proper error handling.
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
     *
     * @param Carbon\Carbon $newStartDate
     * @param Carbon\Carbon $newEndDate
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
     */
    public function getTargetingSummary(): array
    {
        return ['categories_count' => $this->targetCategories()->count(), 'products_count' => $this->targetProducts()->count(), 'customer_groups_count' => $this->targetCustomerGroups()->count(), 'has_audience_targeting' => ! empty($this->target_audience), 'has_segment_targeting' => ! empty($this->target_segments)];
    }

    /**
     * Handle getContentSummary functionality with proper error handling.
     */
    public function getContentSummary(): array
    {
        return ['has_subject' => ! empty($this->subject), 'has_content' => ! empty($this->content), 'has_cta' => ! empty($this->cta_text) && ! empty($this->cta_url), 'has_banner' => ! empty($this->banner_image), 'content_length' => strlen(strip_tags($this->content ?? '')), 'subject_length' => strlen($this->subject ?? '')];
    }

    /**
     * Handle getAutomationSummary functionality with proper error handling.
     */
    public function getAutomationSummary(): array
    {
        return ['auto_start' => $this->auto_start, 'auto_end' => $this->auto_end, 'auto_pause_on_budget' => $this->auto_pause_on_budget, 'send_notifications' => $this->send_notifications, 'track_conversions' => $this->track_conversions, 'is_featured' => $this->is_featured, 'social_media_ready' => $this->social_media_ready];
    }
}
