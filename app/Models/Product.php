<?php declare(strict_types=1);

namespace App\Models;

use App\Observers\ProductObserver;
use App\Traits\HasProductPricing;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

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
        'price',
        'sale_price',
        'manage_stock',
        'stock_quantity',
        'low_stock_threshold',
        'weight',
        'length',
        'width',
        'height',
        'is_visible',
        'is_featured',
        'published_at',
        'seo_title',
        'seo_description',
        'brand_id',
        'status',
        'type',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'is_visible' => 'boolean',
        'is_featured' => 'boolean',
        'manage_stock' => 'boolean',
        'published_at' => 'datetime',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    protected $table = 'products';
    protected string $translationModel = \App\Models\Translations\ProductTranslation::class;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'description', 'sku', 'price', 'sale_price', 'stock_quantity', 'is_visible'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Product {$eventName}")
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

    public function availableQuantity(): int
    {
        if (!$this->manage_stock) {
            return 999;  // Unlimited when not managing stock
        }

        return max($this->stock_quantity - $this->reservedQuantity(), 0);
    }

    public function isOutOfStock(): bool
    {
        return $this->availableQuantity() < 1;
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

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function inventories(): MorphMany
    {
        return $this->morphMany(Inventory::class, 'inventoriable');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
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

    public function getMainImageAttribute(): ?string
    {
        return $this->getFirstMediaUrl('images', 'preview') ?: null;
    }

    public function getThumbnailAttribute(): ?string
    {
        return $this->getFirstMediaUrl('images', 'thumb') ?: null;
    }

    public function getImageUrl(?string $size = null): ?string
    {
        if (!$size) {
            return $this->getFirstMediaUrl('images') ?: null;
        }

        return $this->getFirstMediaUrl('images', "image-{$size}") ?: $this->getFirstMediaUrl('images');
    }

    public function getGalleryImages(): array
    {
        return $this->getMedia('images')->map(function ($media) {
            return [
                'original' => $media->getFullUrl(),
                'xl' => $media->getFullUrl('image-xl'),
                'lg' => $media->getFullUrl('image-lg'),
                'md' => $media->getFullUrl('image-md'),
                'sm' => $media->getFullUrl('image-sm'),
                'xs' => $media->getFullUrl('image-xs'),
                'alt' => $media->getCustomProperty('alt_text') ?: $media->name,
                'title' => $media->name,
                'generated' => $media->getCustomProperty('generated', false),
            ];
        })->toArray();
    }

    public function getMainImage(?string $conversion = 'image-md'): ?string
    {
        return $this->getFirstMediaUrl('images', $conversion) ?: null;
    }

    public function getAllImageSizes(): array
    {
        $mainMedia = $this->getFirstMedia('images');
        
        if (!$mainMedia) {
            return [];
        }

        return [
            'original' => $mainMedia->getFullUrl(),
            'xl' => $mainMedia->getFullUrl('image-xl'),
            'lg' => $mainMedia->getFullUrl('image-lg'),
            'md' => $mainMedia->getFullUrl('image-md'),
            'sm' => $mainMedia->getFullUrl('image-sm'),
            'xs' => $mainMedia->getFullUrl('image-xs'),
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
            $images['xs'] . ' 150w',
            $images['sm'] . ' 300w',
            $images['md'] . ' 500w',
            $images['lg'] . ' 800w',
            $images['xl'] . ' 1200w',
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
        return $this->getMedia('images')->isNotEmpty();
    }

    public function getImagesCount(): int
    {
        return $this->getMedia('images')->count();
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'])
            ->singleFile(false);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        // Product image conversions with multiple resolutions
        $this
            ->addMediaConversion('image-xs')
            ->performOnCollections('images')
            ->width(150)
            ->height(150)
            ->format('webp')
            ->quality(85)
            ->sharpen(10)
            ->optimize();

        $this
            ->addMediaConversion('image-sm')
            ->performOnCollections('images')
            ->width(300)
            ->height(300)
            ->format('webp')
            ->quality(85)
            ->sharpen(10)
            ->optimize();

        $this
            ->addMediaConversion('image-md')
            ->performOnCollections('images')
            ->width(500)
            ->height(500)
            ->format('webp')
            ->quality(85)
            ->sharpen(10)
            ->optimize();

        $this
            ->addMediaConversion('image-lg')
            ->performOnCollections('images')
            ->width(800)
            ->height(800)
            ->format('webp')
            ->quality(90)
            ->sharpen(5)
            ->optimize();

        $this
            ->addMediaConversion('image-xl')
            ->performOnCollections('images')
            ->width(1200)
            ->height(1200)
            ->format('webp')
            ->quality(90)
            ->sharpen(5)
            ->optimize();

        // Legacy conversions for backward compatibility - now in WebP
        $this
            ->addMediaConversion('thumb')
            ->performOnCollections('images')
            ->width(300)
            ->height(300)
            ->format('webp')
            ->quality(85)
            ->sharpen(10)
            ->optimize();

        $this
            ->addMediaConversion('preview')
            ->performOnCollections('images')
            ->width(500)
            ->height(500)
            ->format('webp')
            ->quality(85)
            ->sharpen(10)
            ->optimize();
    }
}
