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

#[ObservedBy([ProductObserver::class])]
final class Product extends Model implements HasMedia
{
    use HasFactory, SoftDeletes;
    use HasProductPricing;
    use HasTranslations;
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

    protected function casts(): array
    {
        return [
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
    }

    protected $table = 'products';
    protected string $translationModel = \App\Models\Translations\ProductTranslation::class;

    public function isPublished(): bool
    {
        return $this->is_visible && $this->published_at && $this->published_at <= now();
    }

    public function reservedQuantity(): int
    {
        $productId = (int) $this->id;

        $sum = (int) DB::table('sh_product_variants as v')
            ->join('sh_variant_inventories as vi', 'vi.variant_id', '=', 'v.id')
            ->where('v.product_id', $productId)
            ->sum('vi.reserved');

        if ($sum === 0) {
            $sum = (int) DB::table('sh_variant_inventories as vi')
                ->where('vi.variant_id', $productId)
                ->sum('vi.reserved');
        }

        return max($sum, 0);
    }

    public function availableQuantity(): int
    {
        $productId = (int) $this->id;

        $sum = (int) DB::table('sh_product_variants as v')
            ->join('sh_variant_inventories as vi', 'vi.variant_id', '=', 'v.id')
            ->where('v.product_id', $productId)
            ->sum(DB::raw('CASE WHEN (vi.stock - vi.reserved) > 0 THEN (vi.stock - vi.reserved) ELSE 0 END'));

        if ($sum === 0) {
            $sum = (int) DB::table('sh_variant_inventories as vi')
                ->where('vi.variant_id', $productId)
                ->sum(DB::raw('CASE WHEN (vi.stock - vi.reserved) > 0 THEN (vi.stock - vi.reserved) ELSE 0 END'));
        }

        return max($sum, 0);
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

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(
            Attribute::class, 
            'sh_product_attributes',
            'product_id',
            'attribute_id'
        )->withTimestamps();
    }

    public function scopePublished($query)
    {
        return $query->where('is_visible', true)
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

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10);

        $this
            ->addMediaConversion('preview')
            ->width(500)
            ->height(500)
            ->sharpen(10);
    }
}
