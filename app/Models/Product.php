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
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy([ProductObserver::class])]
final class Product extends Model implements HasMedia
{
    use HasFactory, SoftDeletes;
    use HasProductPricing;
    use HasTranslations;
    use LogsActivity;
    use InteractsWithMedia;

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
        if (!$img) {
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
            ($images['xs'] ?? null) ? ($images['xs'] . ' 150w') : null,
            ($images['sm'] ?? null) ? ($images['sm'] . ' 300w') : null,
            ($images['md'] ?? null) ? ($images['md'] . ' 500w') : null,
            ($images['lg'] ?? null) ? ($images['lg'] . ' 800w') : null,
            ($images['xl'] ?? null) ? ($images['xl'] . ' 1200w') : null,
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
}
