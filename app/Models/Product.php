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

#[ObservedBy([ProductObserver::class])]
#[ScopedBy([ActiveScope::class, PublishedScope::class, VisibleScope::class])]
final /**
 * Product
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class Product extends Model implements HasMedia
{
    use HasFactory, SoftDeletes;
    use HasProductPricing;
    use HasTranslations;
    use InteractsWithMedia;
    use LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'sku',
        'barcode',
        'price',
        'compare_price',
        'cost_price',
        'sale_price',
        'manage_stock',
        'track_stock',
        'allow_backorder',
        'stock_quantity',
        'low_stock_threshold',
        'weight',
        'length',
        'width',
        'height',
        'is_visible',
        'is_featured',
        'is_requestable',
        'requests_count',
        'minimum_quantity',
        'hide_add_to_cart',
        'request_message',
        'published_at',
        'seo_title',
        'seo_description',
        'brand_id',
        'status',
        'type',
        'video_url',
        'metadata',
        'sort_order',
        'tax_class',
        'shipping_class',
        'download_limit',
        'download_expiry',
        'external_url',
        'button_text',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'is_visible' => 'boolean',
        'is_featured' => 'boolean',
        'is_requestable' => 'boolean',
        'hide_add_to_cart' => 'boolean',
        'manage_stock' => 'boolean',
        'track_stock' => 'boolean',
        'allow_backorder' => 'boolean',
        'published_at' => 'datetime',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'requests_count' => 'integer',
        'minimum_quantity' => 'integer',
        'sort_order' => 'integer',
        'download_limit' => 'integer',
        'download_expiry' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'average_rating',
        'reviews_count',
        'main_image',
        'thumbnail',
        'stock_status',
        'is_in_stock',
        'is_low_stock',
        'is_out_of_stock',
        'available_quantity',
        'discount_percentage',
        'profit_margin',
        'markup_percentage',
        'dimensions',
        'volume',
        'canonical_url',
        'sales_count',
        'revenue',
        'formatted_price',
        'formatted_compare_price',
    ];

    protected $table = 'products';

    protected string $translationModel = \App\Models\Translations\ProductTranslation::class;

    // Translation fields that should be handled by the translation system
    protected array $translatable = [
        'name',
        'slug',
        'description',
        'short_description',
        'seo_title',
        'seo_description',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'description', 'sku', 'price', 'sale_price', 'stock_quantity', 'is_visible'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Product {$eventName}")
            ->useLogName('product');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isPublished(): bool
    {
        return $this->is_visible && $this->published_at && $this->published_at <= now();
    }

    public function reservedQuantity(): int
    {
        // For simple products, no reservations for now
        return 0;
    }

    public function isInStock(): bool
    {
        if (! $this->manage_stock) {
            return true; // Always in stock if not tracking
        }

        return $this->availableQuantity() > 0;
    }

    public function isLowStock(): bool
    {
        if (! $this->manage_stock) {
            return false; // Never low stock if not tracking
        }

        return $this->stock_quantity <= $this->low_stock_threshold;
    }

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

    public function decreaseStock(int $quantity): bool
    {
        if (! $this->manage_stock) {
            return true; // Always allow if not tracking
        }

        if ($this->availableQuantity() < $quantity) {
            return false; // Not enough stock
        }

        $this->decrement('stock_quantity', $quantity);

        return true;
    }

    public function increaseStock(int $quantity): void
    {
        if ($this->manage_stock) {
            $this->increment('stock_quantity', $quantity);
        }
    }

    public function availableQuantity(): int
    {
        if (! $this->manage_stock) {
            return 999;  // Unlimited when not managing stock
        }

        return max($this->stock_quantity - $this->reservedQuantity(), 0);
    }

    public function isOutOfStock(): bool
    {
        return $this->availableQuantity() < 1;
    }

    public function isVariant(): bool
    {
        return $this->type === 'variable' || $this->variants()->exists();
    }

    public function getStockAttribute(): int
    {
        return (int) ($this->stock_quantity ?? 0);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    /**
     * Get the product's latest variant.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestVariant(): HasOne
    {
        return $this->variants()->one()->latestOfMany();
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function prices(): MorphMany
    {
        return $this->morphMany(Price::class, 'priceable');
    }

    /**
     * Get the product's latest price.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function latestPrice(): MorphOne
    {
        return $this->morphOne(Price::class, 'priceable')->latestOfMany();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'product_collections');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the product's latest review.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestReview(): HasOne
    {
        return $this->reviews()->one()->latestOfMany();
    }

    /**
     * Get the product's oldest review.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function oldestReview(): HasOne
    {
        return $this->reviews()->one()->oldestOfMany();
    }

    /**
     * Get the product's highest rated review.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function highestRatedReview(): HasOne
    {
        return $this->reviews()->one()->ofMany('rating', 'max');
    }

    /**
     * Get the product's lowest rated review.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lowestRatedReview(): HasOne
    {
        return $this->reviews()->one()->ofMany('rating', 'min');
    }

    /**
     * Get the product's latest approved review.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestApprovedReview(): HasOne
    {
        return $this->reviews()->one()->ofMany(['created_at' => 'max'], function ($query) {
            $query->where('is_approved', true);
        });
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Get the product's latest image.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestImage(): HasOne
    {
        return $this->images()->one()->latestOfMany();
    }

    /**
     * Get the product's oldest image.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function oldestImage(): HasOne
    {
        return $this->images()->one()->oldestOfMany();
    }

    /**
     * Get the product's primary image (lowest sort order).
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function primaryImage(): HasOne
    {
        return $this->images()->one()->ofMany('sort_order', 'min');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Get the product's latest inventory update.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestInventory(): HasOne
    {
        return $this->inventories()->one()->latestOfMany();
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Get the product's latest document.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function latestDocument(): MorphOne
    {
        return $this->morphOne(Document::class, 'documentable')->latestOfMany();
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ProductRequest::class);
    }

    /**
     * Get the product's latest request.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestRequest(): HasOne
    {
        return $this->requests()->one()->latestOfMany();
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(
            Attribute::class,
            'product_attributes',
            'product_id',
            'attribute_id'
        )->withTimestamps();
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ProductHistory::class);
    }

    /**
     * Get the product's latest history entry.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestHistory(): HasOne
    {
        return $this->histories()->one()->latestOfMany();
    }

    /**
     * Get the product's latest price change.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestPriceChange(): HasOne
    {
        return $this->histories()->one()->ofMany(['created_at' => 'max'], function ($query) {
            $query->where('field_name', 'price');
        });
    }

    /**
     * Get the product's latest stock update.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestStockUpdate(): HasOne
    {
        return $this->histories()->one()->ofMany(['created_at' => 'max'], function ($query) {
            $query->where('field_name', 'stock_quantity');
        });
    }

    /**
     * Get the product's current active price.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function currentPrice(): HasOne
    {
        return $this->histories()->one()->ofMany([
            'created_at' => 'max',
            'id' => 'max',
        ], function ($query) {
            $query->where('field_name', 'price')
                  ->where('created_at', '<=', now());
        });
    }

    public function recentHistories(): HasMany
    {
        return $this->hasMany(ProductHistory::class)->recent(30);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(ProductHistory::class)->byAction('price_changed');
    }

    public function stockHistories(): HasMany
    {
        return $this->hasMany(ProductHistory::class)->byAction('stock_updated');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ProductHistory::class)->byAction('status_changed');
    }

    public function significantHistories(): HasMany
    {
        return $this->hasMany(ProductHistory::class)->whereIn('field_name', [
            'price', 'sale_price', 'stock_quantity', 'status', 'is_visible'
        ]);
    }

    public function getLastPriceChange(): ?ProductHistory
    {
        return $this->latestPriceChange;
    }

    public function getLastStockUpdate(): ?ProductHistory
    {
        return $this->latestStockUpdate;
    }

    public function getLastStatusChange(): ?ProductHistory
    {
        return $this->statusHistories()->latest()->first();
    }

    public function getChangeCount(int $days = 30): int
    {
        return $this->histories()->recent($days)->count();
    }

    public function getPriceChangeCount(int $days = 30): int
    {
        return $this->priceHistories()->recent($days)->count();
    }

    public function getStockChangeCount(int $days = 30): int
    {
        return $this->stockHistories()->recent($days)->count();
    }

    public function hasRecentChanges(int $days = 7): bool
    {
        return $this->histories()->recent($days)->exists();
    }

    public function getChangeFrequency(int $days = 30): float
    {
        $changeCount = $this->getChangeCount($days);
        return $changeCount > 0 ? round($changeCount / $days, 2) : 0;
    }

    public function scopePublished($query)
    {
        return $query
            ->where('is_visible', true)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeByBrand($query, int $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->whereHas('categories', function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId);
        });
    }

    public function scopeByCollection($query, int $collectionId)
    {
        return $query->whereHas('collections', function ($q) use ($collectionId) {
            $q->where('collection_id', $collectionId);
        });
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock_quantity <= low_stock_threshold');
    }

    public function scopeRequestable($query)
    {
        return $query->where('is_requestable', true);
    }

    public function scopeNeedsRestocking($query)
    {
        return $query->where('manage_stock', true)
            ->whereRaw('stock_quantity < minimum_quantity');
    }

    public function scopeWithRequests($query)
    {
        return $query->where('requests_count', '>', 0);
    }

    public function getAverageRatingAttribute(): float
    {
        return $this->reviews()->approved()->avg('rating') ?: 0;
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->approved()->count();
    }

    public function hasVariants(): bool
    {
        return $this->type === 'variable' && $this->variants()->exists();
    }

    public function isRequestable(): bool
    {
        return $this->is_requestable;
    }

    public function shouldHideAddToCart(): bool
    {
        return $this->hide_add_to_cart || $this->is_requestable;
    }

    public function getRequestsCount(): int
    {
        return $this->requests_count;
    }

    public function incrementRequestsCount(): void
    {
        $this->increment('requests_count');
    }

    public function decrementRequestsCount(): void
    {
        $this->decrement('requests_count');
    }

    public function getMinimumQuantity(): int
    {
        return $this->minimum_quantity;
    }

    public function isBelowMinimumQuantity(): bool
    {
        if (! $this->manage_stock) {
            return false;
        }

        return $this->stock_quantity < $this->minimum_quantity;
    }

    public function needsRestocking(): bool
    {
        return $this->isBelowMinimumQuantity();
    }

    public function getMainImageAttribute(): ?string
    {
        $img = $this->images()->orderBy('sort_order')->first();

        return $img ? $this->resolvePublicUrl($img->path) : null;
    }

    public function getThumbnailAttribute(): ?string
    {
        $img = $this->images()->orderBy('sort_order')->first();

        return $img ? $this->resolvePublicUrl($img->path) : null;
    }

    public function getImageUrl(?string $size = null): ?string
    {
        $img = $this->images()->orderBy('sort_order')->first();

        return $img ? $this->resolvePublicUrl($img->path) : null;
    }

    public function getGalleryImages(): array
    {
        return $this->images()->orderBy('sort_order')->get()->map(function (ProductImage $img) {
            $url = $this->resolvePublicUrl($img->path);

            return [
                'original' => $url,
                'xl' => $url,
                'lg' => $url,
                'md' => $url,
                'sm' => $url,
                'xs' => $url,
                'alt' => $img->alt_text ?: $this->name,
                'title' => $this->name,
                'generated' => true,
            ];
        })->toArray();
    }

    public function getMainImage(?string $conversion = 'image-md'): ?string
    {
        return $this->getFirstMediaUrl('images', $conversion) ?: null;
    }

    public function getAllImageSizes(): array
    {
        $img = $this->images()->orderBy('sort_order')->first();
        if (! $img) {
            return [];
        }
        $url = $this->resolvePublicUrl($img->path);

        return [
            'original' => $url,
            'xl' => $url,
            'lg' => $url,
            'md' => $url,
            'sm' => $url,
            'xs' => $url,
        ];
    }

    public function getResponsiveImageAttributes(?string $defaultSize = 'md'): array
    {
        $images = $this->getAllImageSizes();

        if (empty($images)) {
            return [
                'src' => null,
                'srcset' => '',
                'sizes' => '',
                'alt' => $this->name,
            ];
        }

        $srcset = [
            ($images['xs'] ?? null) ? ($images['xs'].' 150w') : null,
            ($images['sm'] ?? null) ? ($images['sm'].' 300w') : null,
            ($images['md'] ?? null) ? ($images['md'].' 500w') : null,
            ($images['lg'] ?? null) ? ($images['lg'].' 800w') : null,
            ($images['xl'] ?? null) ? ($images['xl'].' 1200w') : null,
        ];

        return [
            'src' => $images[$defaultSize] ?? $images['md'],
            'srcset' => implode(', ', array_filter($srcset)),
            'sizes' => '(max-width: 640px) 50vw, (max-width: 1024px) 33vw, 300px',
            'alt' => __('translations.product_image_alt', ['name' => $this->name, 'number' => 1]),
        ];
    }

    public function hasImages(): bool
    {
        return $this->images()->exists();
    }

    public function getImagesCount(): int
    {
        return (int) $this->images()->count();
    }

    // Media library removed for product images in favor of product_images table

    // Media conversions removed

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
    public function getTranslatedName(?string $locale = null): ?string
    {
        return $this->trans('name', $locale) ?: $this->name;
    }

    public function getTranslatedDescription(?string $locale = null): ?string
    {
        return $this->trans('description', $locale) ?: $this->description;
    }

    public function getTranslatedShortDescription(?string $locale = null): ?string
    {
        return $this->trans('summary', $locale) ?: $this->short_description;
    }

    public function getTranslatedSeoTitle(?string $locale = null): ?string
    {
        return $this->trans('seo_title', $locale) ?: $this->seo_title;
    }

    public function getTranslatedSeoDescription(?string $locale = null): ?string
    {
        return $this->trans('seo_description', $locale) ?: $this->seo_description;
    }

    public function getTranslatedSlug(?string $locale = null): ?string
    {
        return $this->trans('slug', $locale) ?: $this->slug;
    }

    // Scope for translated products
    public function scopeWithTranslations($query, ?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        return $query->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }]);
    }

    // Get all available locales for this product
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }

    // Check if product has translation for specific locale
    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    // Get or create translation for locale
    public function getOrCreateTranslation(string $locale): \App\Models\Translations\ProductTranslation
    {
        return $this->translations()->firstOrCreate(
            ['locale' => $locale],
            [
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'summary' => $this->short_description,
                'seo_title' => $this->seo_title,
                'seo_description' => $this->seo_description,
            ]
        );
    }

    // Update translation for specific locale
    public function updateTranslation(string $locale, array $data): bool
    {
        $translation = $this->getOrCreateTranslation($locale);

        return $translation->update($data);
    }

    // Delete translation for specific locale
    public function deleteTranslation(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->delete() > 0;
    }

    // Related products methods
    public function getRelatedProducts(int $limit = 4): \Illuminate\Database\Eloquent\Collection
    {
        $categoryIds = $this->categories->pluck('id')->toArray();
        $brandId = $this->brand_id;

        if (empty($categoryIds) && ! $brandId) {
            return collect();
        }

        $query = Product::published()
            ->where('id', '!=', $this->id)
            ->with(['media', 'brand', 'categories', 'translations']);

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

            $brandProducts = Product::published()
                ->where('brand_id', $brandId)
                ->whereNotIn('id', $existingIds)
                ->with(['media', 'brand', 'categories', 'translations'])
                ->limit($remainingLimit)
                ->get();

            $relatedProducts = $relatedProducts->merge($brandProducts);
        }

        // If still not enough, fill with featured products
        if ($relatedProducts->count() < $limit) {
            $remainingLimit = $limit - $relatedProducts->count();
            $existingIds = $relatedProducts->pluck('id')->toArray();
            $existingIds[] = $this->id;

            $featuredProducts = Product::published()
                ->featured()
                ->whereNotIn('id', $existingIds)
                ->with(['media', 'brand', 'categories', 'translations'])
                ->limit($remainingLimit)
                ->get();

            $relatedProducts = $relatedProducts->merge($featuredProducts);
        }

        return $relatedProducts->take($limit);
    }

    public function getRelatedProductsByCategory(int $limit = 4): \Illuminate\Database\Eloquent\Collection
    {
        $categoryIds = $this->categories->pluck('id')->toArray();

        if (empty($categoryIds)) {
            return collect();
        }

        return Product::published()
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('category_id', $categoryIds);
            })
            ->where('id', '!=', $this->id)
            ->with(['media', 'brand', 'categories', 'translations'])
            ->limit($limit)
            ->get();
    }

    public function getRelatedProductsByBrand(int $limit = 4): \Illuminate\Database\Eloquent\Collection
    {
        if (! $this->brand_id) {
            return collect();
        }

        return Product::published()
            ->where('brand_id', $this->brand_id)
            ->where('id', '!=', $this->id)
            ->with(['media', 'brand', 'categories', 'translations'])
            ->limit($limit)
            ->get();
    }

    public function getRelatedProductsByPriceRange(float $priceRange = 0.2, int $limit = 4): \Illuminate\Database\Eloquent\Collection
    {
        $currentPrice = $this->getPrice()?->value?->amount ?? $this->price;

        if (! $currentPrice) {
            return collect();
        }

        $minPrice = $currentPrice * (1 - $priceRange);
        $maxPrice = $currentPrice * (1 + $priceRange);

        return Product::published()
            ->where('id', '!=', $this->id)
            ->where(function ($query) use ($minPrice, $maxPrice) {
                $query->whereBetween('price', [$minPrice, $maxPrice])
                    ->orWhereBetween('sale_price', [$minPrice, $maxPrice]);
            })
            ->with(['media', 'brand', 'categories', 'translations'])
            ->limit($limit)
            ->get();
    }

    // Advanced Helper Methods
    public function getProductInfo(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'compare_price' => $this->compare_price,
            'cost_price' => $this->cost_price,
            'status' => $this->status,
            'type' => $this->type,
            'is_visible' => $this->is_visible,
            'is_featured' => $this->is_featured,
            'published_at' => $this->published_at?->toISOString(),
        ];
    }

    public function getInventoryInfo(): array
    {
        return [
            'stock_quantity' => $this->stock_quantity,
            'manage_stock' => $this->manage_stock,
            'track_stock' => $this->track_stock,
            'allow_backorder' => $this->allow_backorder,
            'low_stock_threshold' => $this->low_stock_threshold,
            'minimum_quantity' => $this->minimum_quantity,
            'stock_status' => $this->getStockStatus(),
            'is_in_stock' => $this->isInStock(),
            'is_low_stock' => $this->isLowStock(),
            'is_out_of_stock' => $this->isOutOfStock(),
            'available_quantity' => $this->availableQuantity(),
            'reserved_quantity' => $this->reservedQuantity(),
        ];
    }

    public function getPricingInfo(): array
    {
        return [
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'compare_price' => $this->compare_price,
            'cost_price' => $this->cost_price,
            'current_price' => $this->sale_price ?: $this->price,
            'discount_percentage' => $this->getDiscountPercentage(),
            'profit_margin' => $this->getProfitMargin(),
            'markup_percentage' => $this->getMarkupPercentage(),
        ];
    }

    public function getPhysicalInfo(): array
    {
        return [
            'weight' => $this->weight,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'dimensions' => $this->getDimensions(),
            'volume' => $this->getVolume(),
        ];
    }

    public function getSeoInfo(): array
    {
        return [
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'meta_keywords' => $this->meta_keywords ?? [],
            'canonical_url' => $this->getCanonicalUrl(),
        ];
    }

    public function getBusinessInfo(): array
    {
        return [
            'is_featured' => $this->is_featured,
            'is_requestable' => $this->is_requestable,
            'requests_count' => $this->requests_count,
            'average_rating' => $this->average_rating,
            'reviews_count' => $this->reviews_count,
            'views_count' => $this->views_count ?? 0,
            'sales_count' => $this->getSalesCount(),
            'revenue' => $this->getRevenue(),
        ];
    }

    public function getCompleteInfo(?string $locale = null): array
    {
        return array_merge(
            $this->getProductInfo(),
            $this->getInventoryInfo(),
            $this->getPricingInfo(),
            $this->getPhysicalInfo(),
            $this->getSeoInfo(),
            $this->getBusinessInfo(),
            [
                'translations' => $this->getAvailableLocales(),
                'has_translations' => count($this->getAvailableLocales()) > 0,
                'brand' => $this->brand?->name,
                'categories' => $this->categories->pluck('name')->toArray(),
                'collections' => $this->collections->pluck('name')->toArray(),
                'images_count' => $this->getImagesCount(),
                'variants_count' => $this->variants()->count(),
                'attributes_count' => $this->attributes()->count(),
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
            ]
        );
    }

    // Additional helper methods
    public function getDiscountPercentage(): ?float
    {
        if (!$this->sale_price || !$this->price) {
            return null;
        }

        return round((($this->price - $this->sale_price) / $this->price) * 100, 2);
    }

    public function getProfitMargin(): ?float
    {
        if (!$this->cost_price || !$this->price) {
            return null;
        }

        return round((($this->price - $this->cost_price) / $this->price) * 100, 2);
    }

    public function getMarkupPercentage(): ?float
    {
        if (!$this->cost_price || !$this->price) {
            return null;
        }

        return round((($this->price - $this->cost_price) / $this->cost_price) * 100, 2);
    }

    public function getDimensions(): ?string
    {
        if (!$this->length || !$this->width || !$this->height) {
            return null;
        }

        return "{$this->length} × {$this->width} × {$this->height} cm";
    }

    public function getVolume(): ?float
    {
        if (!$this->length || !$this->width || !$this->height) {
            return null;
        }

        return round(($this->length * $this->width * $this->height) / 1000000, 2); // Convert to cubic meters
    }

    public function getCanonicalUrl(): string
    {
        return route('products.show', $this);
    }

    public function getSalesCount(): int
    {
        return $this->orderItems()->sum('quantity');
    }

    public function getRevenue(): float
    {
        return $this->orderItems()->sum(DB::raw('quantity * price'));
    }

    public function getFullDisplayName(?string $locale = null): string
    {
        $name = $this->getTranslatedName($locale);
        $sku = $this->sku ? " ({$this->sku})" : '';
        return $name . $sku;
    }

    /**
     * Get formatted price string using the current currency
     */
    public function getFormattedPrice(): string
    {
        $price = $this->getPrice();
        if (!$price || !$price->value) {
            return app_money_format($this->price ?? 0);
        }
        
        return app_money_format($price->value->amount);
    }

    /**
     * Get formatted compare price string using the current currency
     */
    public function getFormattedComparePrice(): string
    {
        $price = $this->getPrice();
        if (!$price || !$price->compare) {
            return app_money_format($this->compare_price ?? 0);
        }
        
        return app_money_format($price->compare);
    }

    /**
     * Accessor for formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->getFormattedPrice();
    }

    /**
     * Accessor for formatted compare price
     */
    public function getFormattedComparePriceAttribute(): string
    {
        return $this->getFormattedComparePrice();
    }
}
