<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\PublishedScope;
use App\Models\Scopes\VisibleScope;
use App\Observers\ProductObserver;
use App\Traits\HasProductPricing;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Product
 *
 * Eloquent model representing the Product entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $appends
 * @property mixed $table
 * @property string $translationModel
 * @property array $translatable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 *
 * @mixin \Eloquent
 */
#[ObservedBy([ProductObserver::class])]
#[ScopedBy([ActiveScope::class, PublishedScope::class, VisibleScope::class])]
final class Product extends Model implements HasMedia
{
    use HasFactory, SoftDeletes;
    use HasProductPricing;
    use HasTranslations;
    use InteractsWithMedia;
    use LogsActivity;

    protected $fillable = ['name', 'slug', 'description', 'short_description', 'sku', 'barcode', 'price', 'compare_price', 'cost_price', 'sale_price', 'manage_stock', 'track_stock', 'allow_backorder', 'stock_quantity', 'low_stock_threshold', 'weight', 'length', 'width', 'height', 'is_visible', 'is_featured', 'is_requestable', 'requests_count', 'minimum_quantity', 'hide_add_to_cart', 'request_message', 'published_at', 'seo_title', 'seo_description', 'brand_id', 'status', 'type', 'video_url', 'metadata', 'sort_order', 'tax_class', 'shipping_class', 'download_limit', 'download_expiry', 'external_url', 'button_text'];

    protected $casts = ['price' => 'decimal:2', 'compare_price' => 'decimal:2', 'cost_price' => 'decimal:2', 'sale_price' => 'decimal:2', 'weight' => 'decimal:2', 'length' => 'decimal:2', 'width' => 'decimal:2', 'height' => 'decimal:2', 'is_visible' => 'boolean', 'is_featured' => 'boolean', 'is_requestable' => 'boolean', 'hide_add_to_cart' => 'boolean', 'manage_stock' => 'boolean', 'track_stock' => 'boolean', 'allow_backorder' => 'boolean', 'published_at' => 'datetime', 'stock_quantity' => 'integer', 'low_stock_threshold' => 'integer', 'requests_count' => 'integer', 'minimum_quantity' => 'integer', 'sort_order' => 'integer', 'download_limit' => 'integer', 'download_expiry' => 'integer', 'metadata' => 'array'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['average_rating', 'reviews_count', 'main_image', 'thumbnail', 'stock_status', 'is_in_stock', 'is_low_stock', 'is_out_of_stock', 'available_quantity', 'discount_percentage', 'profit_margin', 'markup_percentage', 'dimensions', 'volume', 'canonical_url', 'sales_count', 'revenue', 'formatted_price', 'formatted_compare_price'];

    protected $table = 'products';

    protected string $translationModel = \App\Models\Translations\ProductTranslation::class;

    // Translation fields that should be handled by the translation system
    protected array $translatable = ['name', 'slug', 'description', 'short_description', 'seo_title', 'seo_description'];

    /**
     * Handle getActivitylogOptions functionality with proper error handling.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'slug', 'description', 'sku', 'price', 'sale_price', 'stock_quantity', 'is_visible'])->logOnlyDirty()->dontSubmitEmptyLogs()->setDescriptionForEvent(fn (string $eventName) => "Product {$eventName}")->useLogName('product');
    }

    /**
     * Handle getRouteKeyName functionality with proper error handling.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Handle isPublished functionality with proper error handling.
     */
    public function isPublished(): bool
    {
        return $this->is_visible && $this->published_at && $this->published_at <= now();
    }

    /**
     * Handle reservedQuantity functionality with proper error handling.
     */
    public function reservedQuantity(): int
    {
        // For simple products, no reservations for now
        return 0;
    }

    /**
     * Handle isInStock functionality with proper error handling.
     */
    public function isInStock(): bool
    {
        if (! $this->manage_stock) {
            return true;
            // Always in stock if not tracking
        }

        return $this->availableQuantity() > 0;
    }

    /**
     * Handle isLowStock functionality with proper error handling.
     */
    public function isLowStock(): bool
    {
        if (! $this->manage_stock) {
            return false;
            // Never low stock if not tracking
        }

        return $this->stock_quantity <= $this->low_stock_threshold;
    }

    /**
     * Handle getStockStatus functionality with proper error handling.
     */
    public function getStockStatus(): string
    {
        if (! $this->manage_stock) {
            return 'not_tracked';
        }
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        }
        if ($this->isLowStock()) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * Handle getStockStatusAttribute functionality with proper error handling.
     */
    public function getStockStatusAttribute(): string
    {
        return $this->getStockStatus();
    }

    /**
     * Handle getIsInStockAttribute functionality with proper error handling.
     */
    public function getIsInStockAttribute(): bool
    {
        return $this->isInStock();
    }

    /**
     * Handle getIsLowStockAttribute functionality with proper error handling.
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->isLowStock();
    }

    /**
     * Handle getIsOutOfStockAttribute functionality with proper error handling.
     */
    public function getIsOutOfStockAttribute(): bool
    {
        return $this->isOutOfStock();
    }

    /**
     * Handle getAvailableQuantityAttribute functionality with proper error handling.
     */
    public function getAvailableQuantityAttribute(): int
    {
        return $this->availableQuantity();
    }

    /**
     * Handle getDiscountPercentageAttribute functionality with proper error handling.
     */
    public function getDiscountPercentageAttribute(): float
    {
        if (! $this->compare_price || $this->compare_price <= $this->price) {
            return 0.0;
        }

        return round((($this->compare_price - $this->price) / $this->compare_price) * 100, 2);
    }

    /**
     * Handle getProfitMarginAttribute functionality with proper error handling.
     */
    public function getProfitMarginAttribute(): float
    {
        if (! $this->cost_price || $this->cost_price <= 0) {
            return 0.0;
        }

        return round((($this->price - $this->cost_price) / $this->price) * 100, 2);
    }

    /**
     * Handle getMarkupPercentageAttribute functionality with proper error handling.
     */
    public function getMarkupPercentageAttribute(): float
    {
        if (! $this->cost_price || $this->cost_price <= 0) {
            return 0.0;
        }

        return round((($this->price - $this->cost_price) / $this->cost_price) * 100, 2);
    }

    /**
     * Handle getDimensionsAttribute functionality with proper error handling.
     */
    public function getDimensionsAttribute(): array
    {
        return [
            'length' => $this->length ?? 0,
            'width' => $this->width ?? 0,
            'height' => $this->height ?? 0,
        ];
    }

    /**
     * Handle getVolumeAttribute functionality with proper error handling.
     */
    public function getVolumeAttribute(): float
    {
        $dimensions = $this->getDimensionsAttribute();

        return $dimensions['length'] * $dimensions['width'] * $dimensions['height'];
    }

    /**
     * Handle getCanonicalUrlAttribute functionality with proper error handling.
     */
    public function getCanonicalUrlAttribute(): string
    {
        return route('products.show', $this->slug);
    }

    /**
     * Handle getSalesCountAttribute functionality with proper error handling.
     */
    public function getSalesCountAttribute(): int
    {
        // This would need to be implemented based on order items
        return 0;
    }

    /**
     * Handle getRevenueAttribute functionality with proper error handling.
     */
    public function getRevenueAttribute(): float
    {
        // This would need to be implemented based on order items
        return 0.0;
    }

    /**
     * Handle decreaseStock functionality with proper error handling.
     */
    public function decreaseStock(int $quantity): bool
    {
        if (! $this->manage_stock) {
            return true;
            // Always allow if not tracking
        }
        if ($this->availableQuantity() < $quantity) {
            return false;
            // Not enough stock
        }
        $this->decrement('stock_quantity', $quantity);

        return true;
    }

    /**
     * Handle increaseStock functionality with proper error handling.
     */
    public function increaseStock(int $quantity): void
    {
        if ($this->manage_stock) {
            $this->increment('stock_quantity', $quantity);
        }
    }

    /**
     * Handle availableQuantity functionality with proper error handling.
     */
    public function availableQuantity(): int
    {
        if (! $this->manage_stock) {
            return 999;
            // Unlimited when not managing stock
        }

        return max($this->stock_quantity - $this->reservedQuantity(), 0);
    }

    /**
     * Handle isOutOfStock functionality with proper error handling.
     */
    public function isOutOfStock(): bool
    {
        return $this->availableQuantity() < 1;
    }

    /**
     * Handle isVariant functionality with proper error handling.
     */
    public function isVariant(): bool
    {
        return $this->type === 'variable' || $this->variants()->exists();
    }

    /**
     * Handle getStockAttribute functionality with proper error handling.
     */
    public function getStockAttribute(): int
    {
        return (int) ($this->stock_quantity ?? 0);
    }

    /**
     * Handle variants functionality with proper error handling.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    /**
     * Handle latestVariant functionality with proper error handling.
     */
    public function latestVariant(): HasOne
    {
        return $this->variants()->one()->latestOfMany();
    }

    /**
     * Handle brand functionality with proper error handling.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * Handle prices functionality with proper error handling.
     */
    public function prices(): MorphMany
    {
        return $this->morphMany(Price::class, 'priceable');
    }

    /**
     * Handle latestPrice functionality with proper error handling.
     */
    public function latestPrice(): MorphOne
    {
        return $this->morphOne(Price::class, 'priceable')->latestOfMany();
    }

    /**
     * Handle categories functionality with proper error handling.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    /**
     * Handle collections functionality with proper error handling.
     */
    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'product_collections');
    }

    public function userBehaviors(): HasMany
    {
        return $this->hasMany(UserBehavior::class);
    }

    /**
     * Handle reviews functionality with proper error handling.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Handle latestReview functionality with proper error handling.
     */
    public function latestReview(): HasOne
    {
        return $this->reviews()->one()->latestOfMany();
    }

    /**
     * Handle oldestReview functionality with proper error handling.
     */
    public function oldestReview(): HasOne
    {
        return $this->reviews()->one()->oldestOfMany();
    }

    /**
     * Handle highestRatedReview functionality with proper error handling.
     */
    public function highestRatedReview(): HasOne
    {
        return $this->reviews()->one()->ofMany('rating', 'max');
    }

    /**
     * Handle lowestRatedReview functionality with proper error handling.
     */
    public function lowestRatedReview(): HasOne
    {
        return $this->reviews()->one()->ofMany('rating', 'min');
    }

    /**
     * Handle latestApprovedReview functionality with proper error handling.
     */
    public function latestApprovedReview(): HasOne
    {
        return $this->reviews()->one()->ofMany(['created_at' => 'max'], function ($query) {
            $query->where('is_approved', true);
        });
    }

    /**
     * Handle images functionality with proper error handling.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Handle latestImage functionality with proper error handling.
     */
    public function latestImage(): HasOne
    {
        return $this->images()->one()->latestOfMany();
    }

    /**
     * Handle oldestImage functionality with proper error handling.
     */
    public function oldestImage(): HasOne
    {
        return $this->images()->one()->oldestOfMany();
    }

    /**
     * Handle primaryImage functionality with proper error handling.
     */
    public function primaryImage(): HasOne
    {
        return $this->images()->one()->ofMany('sort_order', 'min');
    }

    /**
     * Handle orderItems functionality with proper error handling.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Handle cartItems functionality with proper error handling.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Handle inventories functionality with proper error handling.
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Handle latestInventory functionality with proper error handling.
     */
    public function latestInventory(): HasOne
    {
        return $this->inventories()->one()->latestOfMany();
    }

    /**
     * Handle documents functionality with proper error handling.
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Handle latestDocument functionality with proper error handling.
     */
    public function latestDocument(): MorphOne
    {
        return $this->morphOne(Document::class, 'documentable')->latestOfMany();
    }

    /**
     * Handle requests functionality with proper error handling.
     */
    public function requests(): HasMany
    {
        return $this->hasMany(ProductRequest::class);
    }

    /**
     * Handle latestRequest functionality with proper error handling.
     */
    public function latestRequest(): HasOne
    {
        return $this->requests()->one()->latestOfMany();
    }

    /**
     * Handle attributes functionality with proper error handling.
     */
    public function attributes(): BelongsToMany
    {
        return $this
            ->belongsToMany(Attribute::class, 'product_attributes', 'product_id', 'attribute_id')
            ->withPivot('attribute_value_id')
            ->withTimestamps();
    }

    /**
     * Handle histories functionality with proper error handling.
     */
    public function histories(): HasMany
    {
        return $this->hasMany(ProductHistory::class);
    }

    /**
     * Handle latestHistory functionality with proper error handling.
     */
    public function latestHistory(): HasOne
    {
        return $this->histories()->one()->latestOfMany();
    }

    /**
     * Handle latestPriceChange functionality with proper error handling.
     */
    public function latestPriceChange(): HasOne
    {
        return $this->histories()->one()->ofMany(['created_at' => 'max'], function ($query) {
            $query->where('field_name', 'price');
        });
    }

    /**
     * Handle latestStockUpdate functionality with proper error handling.
     */
    public function latestStockUpdate(): HasOne
    {
        return $this->histories()->one()->ofMany(['created_at' => 'max'], function ($query) {
            $query->where('field_name', 'stock_quantity');
        });
    }

    /**
     * Handle currentPrice functionality with proper error handling.
     */
    public function currentPrice(): HasOne
    {
        return $this->histories()->one()->ofMany(['created_at' => 'max', 'id' => 'max'], function ($query) {
            $query->where('field_name', 'price')->where('created_at', '<=', now());
        });
    }

    /**
     * Handle recentHistories functionality with proper error handling.
     */
    public function recentHistories(): HasMany
    {
        return $this->hasMany(ProductHistory::class)->recent(30);
    }

    /**
     * Handle priceHistories functionality with proper error handling.
     */
    public function priceHistories(): HasMany
    {
        return $this->hasMany(ProductHistory::class)->byAction('price_changed');
    }

    /**
     * Handle stockHistories functionality with proper error handling.
     */
    public function stockHistories(): HasMany
    {
        return $this->hasMany(ProductHistory::class)->byAction('stock_updated');
    }

    /**
     * Handle statusHistories functionality with proper error handling.
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(ProductHistory::class)->byAction('status_changed');
    }

    /**
     * Handle significantHistories functionality with proper error handling.
     */
    public function significantHistories(): HasMany
    {
        return $this->hasMany(ProductHistory::class)->whereIn('field_name', ['price', 'sale_price', 'stock_quantity', 'status', 'is_visible']);
    }

    /**
     * Handle getLastPriceChange functionality with proper error handling.
     */
    public function getLastPriceChange(): ?ProductHistory
    {
        return $this->latestPriceChange;
    }

    /**
     * Handle getLastStockUpdate functionality with proper error handling.
     */
    public function getLastStockUpdate(): ?ProductHistory
    {
        return $this->latestStockUpdate;
    }

    /**
     * Handle getLastStatusChange functionality with proper error handling.
     */
    public function getLastStatusChange(): ?ProductHistory
    {
        return $this->statusHistories()->latest()->first();
    }

    /**
     * Handle getChangeCount functionality with proper error handling.
     */
    public function getChangeCount(int $days = 30): int
    {
        return $this->histories()->recent($days)->count();
    }

    /**
     * Handle getPriceChangeCount functionality with proper error handling.
     */
    public function getPriceChangeCount(int $days = 30): int
    {
        return $this->priceHistories()->recent($days)->count();
    }

    /**
     * Handle getStockChangeCount functionality with proper error handling.
     */
    public function getStockChangeCount(int $days = 30): int
    {
        return $this->stockHistories()->recent($days)->count();
    }

    /**
     * Handle hasRecentChanges functionality with proper error handling.
     */
    public function hasRecentChanges(int $days = 7): bool
    {
        return $this->histories()->recent($days)->exists();
    }

    /**
     * Handle getChangeFrequency functionality with proper error handling.
     */
    public function getChangeFrequency(int $days = 30): float
    {
        $changeCount = $this->getChangeCount($days);

        return $changeCount > 0 ? round($changeCount / $days, 2) : 0;
    }

    /**
     * Handle scopePublished functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopePublished($query)
    {
        return $query->where('is_visible', true)->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /**
     * Handle scopeFeatured functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Handle scopeVisible functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Handle scopeByBrand functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByBrand($query, int $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    /**
     * Handle scopeByCategory functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->whereHas('categories', function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId);
        });
    }

    /**
     * Handle scopeByCollection functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByCollection($query, int $collectionId)
    {
        return $query->whereHas('collections', function ($q) use ($collectionId) {
            $q->where('collection_id', $collectionId);
        });
    }

    /**
     * Handle scopeInStock functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Handle scopeLowStock functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock_quantity <= low_stock_threshold');
    }

    /**
     * Handle scopeRequestable functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeRequestable($query)
    {
        return $query->where('is_requestable', true);
    }

    /**
     * Handle scopeNeedsRestocking functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeNeedsRestocking($query)
    {
        return $query->where('manage_stock', true)->whereRaw('stock_quantity < minimum_quantity');
    }

    /**
     * Handle scopeWithRequests functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeWithRequests($query)
    {
        return $query->where('requests_count', '>', 0);
    }

    /**
     * Handle getAverageRatingAttribute functionality with proper error handling.
     */
    public function getAverageRatingAttribute(): float
    {
        $rating = $this->reviews()->approved()->avg('rating');

        return $rating ? (float) $rating : 0.0;
    }

    /**
     * Handle getReviewsCountAttribute functionality with proper error handling.
     */
    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->approved()->count();
    }

    /**
     * Handle hasVariants functionality with proper error handling.
     */
    public function hasVariants(): bool
    {
        return $this->type === 'variable' && $this->variants()->exists();
    }

    /**
     * Handle isRequestable functionality with proper error handling.
     */
    public function isRequestable(): bool
    {
        return $this->is_requestable;
    }

    /**
     * Handle shouldHideAddToCart functionality with proper error handling.
     */
    public function shouldHideAddToCart(): bool
    {
        return $this->hide_add_to_cart || $this->is_requestable;
    }

    /**
     * Handle getRequestsCount functionality with proper error handling.
     */
    public function getRequestsCount(): int
    {
        return $this->requests_count;
    }

    /**
     * Handle incrementRequestsCount functionality with proper error handling.
     */
    public function incrementRequestsCount(): void
    {
        $this->increment('requests_count');
    }

    /**
     * Handle decrementRequestsCount functionality with proper error handling.
     */
    public function decrementRequestsCount(): void
    {
        $this->decrement('requests_count');
    }

    /**
     * Handle getMinimumQuantity functionality with proper error handling.
     */
    public function getMinimumQuantity(): int
    {
        return $this->minimum_quantity;
    }

    /**
     * Handle isBelowMinimumQuantity functionality with proper error handling.
     */
    public function isBelowMinimumQuantity(): bool
    {
        if (! $this->manage_stock) {
            return false;
        }

        return $this->stock_quantity < $this->minimum_quantity;
    }

    /**
     * Handle needsRestocking functionality with proper error handling.
     */
    public function needsRestocking(): bool
    {
        return $this->isBelowMinimumQuantity();
    }

    /**
     * Handle getMainImageAttribute functionality with proper error handling.
     */
    public function getMainImageAttribute(): ?string
    {
        $img = $this->images()->orderBy('sort_order')->first();

        return $img ? $this->resolvePublicUrl($img->path) : null;
    }

    /**
     * Handle getThumbnailAttribute functionality with proper error handling.
     */
    public function getThumbnailAttribute(): ?string
    {
        $img = $this->images()->orderBy('sort_order')->first();

        return $img ? $this->resolvePublicUrl($img->path) : null;
    }

    /**
     * Handle getImageUrl functionality with proper error handling.
     */
    public function getImageUrl(?string $size = null): ?string
    {
        $img = $this->images()->orderBy('sort_order')->first();

        return $img ? $this->resolvePublicUrl($img->path) : null;
    }

    /**
     * Handle getGalleryImages functionality with proper error handling.
     */
    public function getGalleryImages(): array
    {
        return $this->images()->orderBy('sort_order')->get()->map(function (ProductImage $img) {
            $url = $this->resolvePublicUrl($img->path);

            return ['original' => $url, 'xl' => $url, 'lg' => $url, 'md' => $url, 'sm' => $url, 'xs' => $url, 'alt' => $img->alt_text ?: $this->name, 'title' => $this->name, 'generated' => true];
        })->toArray();
    }

    /**
     * Handle getMainImage functionality with proper error handling.
     */
    public function getMainImage(?string $conversion = 'image-md'): ?string
    {
        return $this->getFirstMediaUrl('images', $conversion) ?: null;
    }

    /**
     * Handle getAllImageSizes functionality with proper error handling.
     */
    public function getAllImageSizes(): array
    {
        $img = $this->images()->orderBy('sort_order')->first();
        if (! $img) {
            return [];
        }
        $url = $this->resolvePublicUrl($img->path);

        return ['original' => $url, 'xl' => $url, 'lg' => $url, 'md' => $url, 'sm' => $url, 'xs' => $url];
    }

    /**
     * Handle getResponsiveImageAttributes functionality with proper error handling.
     */
    public function getResponsiveImageAttributes(?string $defaultSize = 'md'): array
    {
        $images = $this->getAllImageSizes();
        if (empty($images)) {
            return ['src' => null, 'srcset' => '', 'sizes' => '', 'alt' => $this->name];
        }
        $srcset = [$images['xs'] ?? null ? $images['xs'].' 150w' : null, $images['sm'] ?? null ? $images['sm'].' 300w' : null, $images['md'] ?? null ? $images['md'].' 500w' : null, $images['lg'] ?? null ? $images['lg'].' 800w' : null, $images['xl'] ?? null ? $images['xl'].' 1200w' : null];

        return ['src' => $images[$defaultSize] ?? $images['md'], 'srcset' => implode(', ', array_filter($srcset)), 'sizes' => '(max-width: 640px) 50vw, (max-width: 1024px) 33vw, 300px', 'alt' => __('translations.product_image_alt', ['name' => $this->name, 'number' => 1])];
    }

    /**
     * Handle hasImages functionality with proper error handling.
     */
    public function hasImages(): bool
    {
        return $this->images()->exists();
    }

    /**
     * Handle getImagesCount functionality with proper error handling.
     */
    public function getImagesCount(): int
    {
        return (int) $this->images()->count();
    }

    // Media library removed for product images in favor of product_images table
    // Media conversions removed

    /**
     * Handle resolvePublicUrl functionality with proper error handling.
     */
    private function resolvePublicUrl(string $path): string
    {
        // Assume stored under public disk or public path
        $prefixes = ['http://', 'https://', '/'];
        foreach ($prefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $path;
            }
        }

        return asset(trim($path, '/'));
    }

    // Translation methods

    /**
     * Handle getTranslatedName functionality with proper error handling.
     */
    public function getTranslatedName(?string $locale = null): ?string
    {
        return $this->trans('name', $locale) ?: $this->name;
    }

    /**
     * Handle getTranslatedDescription functionality with proper error handling.
     */
    public function getTranslatedDescription(?string $locale = null): ?string
    {
        return $this->trans('description', $locale) ?: $this->description;
    }

    /**
     * Handle getTranslatedShortDescription functionality with proper error handling.
     */
    public function getTranslatedShortDescription(?string $locale = null): ?string
    {
        return $this->trans('summary', $locale) ?: $this->short_description;
    }

    /**
     * Handle getTranslatedSummary functionality with proper error handling.
     */
    public function getTranslatedSummary(?string $locale = null): ?string
    {
        return $this->trans('summary', $locale) ?: ($this->summary ?? $this->short_description);
    }

    /**
     * Handle getTranslatedSeoTitle functionality with proper error handling.
     */
    public function getTranslatedSeoTitle(?string $locale = null): ?string
    {
        return $this->trans('seo_title', $locale) ?: $this->seo_title;
    }

    /**
     * Handle getTranslatedSeoDescription functionality with proper error handling.
     */
    public function getTranslatedSeoDescription(?string $locale = null): ?string
    {
        return $this->trans('seo_description', $locale) ?: $this->seo_description;
    }

    /**
     * Handle getTranslatedSlug functionality with proper error handling.
     */
    public function getTranslatedSlug(?string $locale = null): ?string
    {
        return $this->trans('slug', $locale) ?: $this->slug;
    }

    // Scope for translated products

    /**
     * Handle scopeWithTranslations functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeWithTranslations($query, ?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        return $query->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }]);
    }

    // Get all available locales for this product

    /**
     * Handle getAvailableLocales functionality with proper error handling.
     */
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }

    // Check if product has translation for specific locale

    /**
     * Handle hasTranslationFor functionality with proper error handling.
     */
    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    // Get or create translation for locale

    /**
     * Handle getOrCreateTranslation functionality with proper error handling.
     *
     * @return App\Models\Translations\ProductTranslation
     */
    public function getOrCreateTranslation(string $locale): \App\Models\Translations\ProductTranslation
    {
        return $this->translations()->firstOrCreate(['locale' => $locale], ['name' => $this->name, 'slug' => $this->slug, 'description' => $this->description, 'summary' => $this->short_description, 'seo_title' => $this->seo_title, 'seo_description' => $this->seo_description]);
    }

    // Update translation for specific locale

    /**
     * Handle updateTranslation functionality with proper error handling.
     */
    public function updateTranslation(string $locale, array $data): bool
    {
        $translation = $this->getOrCreateTranslation($locale);

        return $translation->update($data);
    }

    // Delete translation for specific locale

    /**
     * Handle deleteTranslation functionality with proper error handling.
     */
    public function deleteTranslation(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->delete() > 0;
    }

    // Related products methods

    /**
     * Handle getRelatedProducts functionality with proper error handling.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getRelatedProducts(int $limit = 4): \Illuminate\Database\Eloquent\Collection
    {
        $categoryIds = $this->categories->pluck('id')->toArray();
        $brandId = $this->brand_id;
        if (empty($categoryIds) && ! $brandId) {
            return collect();
        }
        $query = Product::published()->where('id', '!=', $this->id)->with(['media', 'brand', 'categories', 'translations']);
        // First try to get products from same categories
        if (! empty($categoryIds)) {
            $query->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('category_id', $categoryIds);
            });
        }
        $relatedProducts = $query->limit($limit)->get();
        // If we don't have enough products from categories, fill with products from same brand
        if ($relatedProducts->count() < $limit && $brandId) {
            $remainingLimit = $limit - $relatedProducts->count();
            $existingIds = $relatedProducts->pluck('id')->toArray();
            $existingIds[] = $this->id;
            $brandProducts = Product::published()->where('brand_id', $brandId)->whereNotIn('id', $existingIds)->with(['media', 'brand', 'categories', 'translations'])->limit($remainingLimit)->get();
            $relatedProducts = $relatedProducts->merge($brandProducts);
        }
        // If still not enough, fill with featured products
        if ($relatedProducts->count() < $limit) {
            $remainingLimit = $limit - $relatedProducts->count();
            $existingIds = $relatedProducts->pluck('id')->toArray();
            $existingIds[] = $this->id;
            $featuredProducts = Product::published()->featured()->whereNotIn('id', $existingIds)->with(['media', 'brand', 'categories', 'translations'])->limit($remainingLimit)->get();
            $relatedProducts = $relatedProducts->merge($featuredProducts);
        }

        return $relatedProducts->take($limit);
    }

    /**
     * Handle getRelatedProductsByCategory functionality with proper error handling.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getRelatedProductsByCategory(int $limit = 4): \Illuminate\Database\Eloquent\Collection
    {
        $categoryIds = $this->categories->pluck('id')->toArray();
        if (empty($categoryIds)) {
            return collect();
        }

        return Product::published()->whereHas('categories', function ($query) use ($categoryIds) {
            $query->whereIn('category_id', $categoryIds);
        })->where('id', '!=', $this->id)->with(['media', 'brand', 'categories', 'translations'])->limit($limit)->get();
    }

    /**
     * Handle getRelatedProductsByBrand functionality with proper error handling.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getRelatedProductsByBrand(int $limit = 4): \Illuminate\Database\Eloquent\Collection
    {
        if (! $this->brand_id) {
            return collect();
        }

        return Product::published()->where('brand_id', $this->brand_id)->where('id', '!=', $this->id)->with(['media', 'brand', 'categories', 'translations'])->limit($limit)->get();
    }

    /**
     * Handle getRelatedProductsByPriceRange functionality with proper error handling.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getRelatedProductsByPriceRange(float $priceRange = 0.2, int $limit = 4): \Illuminate\Database\Eloquent\Collection
    {
        $currentPrice = $this->getPrice()?->value?->amount ?? $this->price;
        if (! $currentPrice) {
            return collect();
        }
        $minPrice = $currentPrice * (1 - $priceRange);
        $maxPrice = $currentPrice * (1 + $priceRange);

        return Product::published()->where('id', '!=', $this->id)->where(function ($query) use ($minPrice, $maxPrice) {
            $query->whereBetween('price', [$minPrice, $maxPrice])->orWhereBetween('sale_price', [$minPrice, $maxPrice]);
        })->with(['media', 'brand', 'categories', 'translations'])->limit($limit)->get();
    }

    // Advanced Helper Methods

    /**
     * Handle getProductInfo functionality with proper error handling.
     */
    public function getProductInfo(): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'slug' => $this->slug, 'sku' => $this->sku, 'description' => $this->description, 'short_description' => $this->short_description, 'price' => $this->price, 'sale_price' => $this->sale_price, 'compare_price' => $this->compare_price, 'cost_price' => $this->cost_price, 'status' => $this->status, 'type' => $this->type, 'is_visible' => $this->is_visible, 'is_featured' => $this->is_featured, 'published_at' => $this->published_at?->toISOString()];
    }

    /**
     * Handle getInventoryInfo functionality with proper error handling.
     */
    public function getInventoryInfo(): array
    {
        return ['stock_quantity' => $this->stock_quantity, 'manage_stock' => $this->manage_stock, 'track_stock' => $this->track_stock, 'allow_backorder' => $this->allow_backorder, 'low_stock_threshold' => $this->low_stock_threshold, 'minimum_quantity' => $this->minimum_quantity, 'stock_status' => $this->getStockStatus(), 'is_in_stock' => $this->isInStock(), 'is_low_stock' => $this->isLowStock(), 'is_out_of_stock' => $this->isOutOfStock(), 'available_quantity' => $this->availableQuantity(), 'reserved_quantity' => $this->reservedQuantity()];
    }

    /**
     * Handle getPricingInfo functionality with proper error handling.
     */
    public function getPricingInfo(): array
    {
        return ['price' => $this->price, 'sale_price' => $this->sale_price, 'compare_price' => $this->compare_price, 'cost_price' => $this->cost_price, 'current_price' => $this->sale_price ?: $this->price, 'discount_percentage' => $this->getDiscountPercentage(), 'profit_margin' => $this->getProfitMargin(), 'markup_percentage' => $this->getMarkupPercentage()];
    }

    /**
     * Handle getPhysicalInfo functionality with proper error handling.
     */
    public function getPhysicalInfo(): array
    {
        return ['weight' => $this->weight, 'length' => $this->length, 'width' => $this->width, 'height' => $this->height, 'dimensions' => $this->getDimensions(), 'volume' => $this->getVolume()];
    }

    /**
     * Handle getSeoInfo functionality with proper error handling.
     */
    public function getSeoInfo(): array
    {
        return ['seo_title' => $this->seo_title, 'seo_description' => $this->seo_description, 'meta_keywords' => $this->meta_keywords ?? [], 'canonical_url' => $this->getCanonicalUrl()];
    }

    /**
     * Handle getBusinessInfo functionality with proper error handling.
     */
    public function getBusinessInfo(): array
    {
        return ['is_featured' => $this->is_featured, 'is_requestable' => $this->is_requestable, 'requests_count' => $this->requests_count, 'average_rating' => $this->average_rating, 'reviews_count' => $this->reviews_count, 'sales_count' => $this->getSalesCount(), 'revenue' => $this->getRevenue()];
    }

    /**
     * Handle getCompleteInfo functionality with proper error handling.
     */
    public function getCompleteInfo(?string $locale = null): array
    {
        return array_merge($this->getProductInfo(), $this->getInventoryInfo(), $this->getPricingInfo(), $this->getPhysicalInfo(), $this->getSeoInfo(), $this->getBusinessInfo(), ['translations' => $this->getAvailableLocales(), 'has_translations' => count($this->getAvailableLocales()) > 0, 'brand' => $this->brand?->name, 'categories' => $this->categories->pluck('name')->toArray(), 'collections' => $this->collections->pluck('name')->toArray(), 'images_count' => $this->getImagesCount(), 'variants_count' => $this->variants()->count(), 'attributes_count' => $this->attributes()->count(), 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()]);
    }

    // Additional helper methods

    /**
     * Handle getDiscountPercentage functionality with proper error handling.
     */
    public function getDiscountPercentage(): ?float
    {
        if (! $this->sale_price || ! $this->price) {
            return null;
        }

        return round(($this->price - $this->sale_price) / $this->price * 100, 2);
    }

    /**
     * Handle getProfitMargin functionality with proper error handling.
     */
    public function getProfitMargin(): ?float
    {
        if (! $this->cost_price || ! $this->price) {
            return null;
        }

        return round(($this->price - $this->cost_price) / $this->price * 100, 2);
    }

    /**
     * Handle getMarkupPercentage functionality with proper error handling.
     */
    public function getMarkupPercentage(): ?float
    {
        if (! $this->cost_price || ! $this->price) {
            return null;
        }

        return round(($this->price - $this->cost_price) / $this->cost_price * 100, 2);
    }

    /**
     * Handle getDimensions functionality with proper error handling.
     */
    public function getDimensions(): ?string
    {
        if (! $this->length || ! $this->width || ! $this->height) {
            return null;
        }

        return "{$this->length} × {$this->width} × {$this->height} cm";
    }

    /**
     * Handle getVolume functionality with proper error handling.
     */
    public function getVolume(): ?float
    {
        if (! $this->length || ! $this->width || ! $this->height) {
            return null;
        }

        return round($this->length * $this->width * $this->height / 1000000, 2);
        // Convert to cubic meters
    }

    /**
     * Handle getCanonicalUrl functionality with proper error handling.
     */
    public function getCanonicalUrl(): string
    {
        return route('products.show', $this);
    }

    /**
     * Handle getSalesCount functionality with proper error handling.
     */
    public function getSalesCount(): int
    {
        return $this->orderItems()->sum('quantity');
    }

    /**
     * Handle getRevenue functionality with proper error handling.
     */
    public function getRevenue(): float
    {
        return $this->orderItems()->sum(DB::raw('quantity * price'));
    }

    /**
     * Handle getFullDisplayName functionality with proper error handling.
     */
    public function getFullDisplayName(?string $locale = null): string
    {
        $name = $this->getTranslatedName($locale);
        $sku = $this->sku ? " ({$this->sku})" : '';

        return $name.$sku;
    }

    /**
     * Handle getFormattedPrice functionality with proper error handling.
     */
    public function getFormattedPrice(): string
    {
        $price = $this->getPrice();
        if (! $price || ! $price->value) {
            return app_money_format($this->price ?? 0);
        }

        return app_money_format($price->value->amount);
    }

    /**
     * Handle getFormattedComparePrice functionality with proper error handling.
     */
    public function getFormattedComparePrice(): string
    {
        $price = $this->getPrice();
        if (! $price || ! $price->compare) {
            return app_money_format($this->compare_price ?? 0);
        }

        return app_money_format($price->compare);
    }

    /**
     * Handle getFormattedPriceAttribute functionality with proper error handling.
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->getFormattedPrice();
    }

    /**
     * Handle getFormattedComparePriceAttribute functionality with proper error handling.
     */
    public function getFormattedComparePriceAttribute(): string
    {
        return $this->getFormattedComparePrice();
    }
}
