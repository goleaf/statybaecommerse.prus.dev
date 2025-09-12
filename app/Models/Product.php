<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\ProductObserver;
use App\Traits\HasProductPricing;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[ObservedBy([ProductObserver::class])]
final class Product extends Model implements HasMedia
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

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function prices(): MorphMany
    {
        return $this->morphMany(Price::class, 'priceable');
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

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
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

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ProductRequest::class);
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

    public function recentHistories(): HasMany
    {
        return $this->hasMany(ProductHistory::class)->recent(30);
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
}
