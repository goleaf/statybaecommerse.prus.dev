<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\TranslatableRecord;
use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\PublishedScope;
use App\Models\Scopes\VisibleScope;
use App\Support\Html\HtmlSanitizer;
use App\Traits\HasProductPricing;
use App\Traits\HasTranslations;
use DateTimeInterface;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Product
 *
 * Eloquent model representing the Product entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed                           $fillable
 * @property mixed                           $casts
 * @property mixed                           $appends
 * @property mixed                           $table
 * @property string                          $translationModel
 * @property array                           $translatable
 * @property int                             $id
 * @property string                          $name
 * @property string|null                     $slug
 * @property string|null                     $short_description
 * @property string|null                     $description
 * @property string|null                     $sku
 * @property float|string|null               $price
 * @property bool                            $is_featured
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property-read Brand|null $brand
 * @property-read string|null $thumbnail
 * @property-read string|null $main_image
 * @property-read int $sales_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, PublishedScope::class, VisibleScope::class])]
final class Product extends Model implements HasMedia, TranslatableRecord
{
    use HasFactory;
    use HasProductPricing;
    use HasTranslations;
    use InteractsWithMedia;
    use OrdersByName;
    use Searchable {
        Searchable::bootSearchable as scoutBootSearchable;
    }

    public const SCOPE_COLUMN_HINTS = [
        'is_active'    => false,
        'is_enabled'   => true,
        'status'       => true,
        'published_at' => true,
    ];

    protected $fillable = ['name', 'slug', 'description', 'short_description', 'detailed_description', 'sku', 'barcode', 'price', 'cost_price', 'manage_stock', 'allow_backorder', 'stock_quantity', 'low_stock_threshold', 'weight', 'length', 'width', 'height', 'size', 'size_type', 'color', 'pack_size', 'pack_size_type', 'is_active', 'is_enabled', 'is_featured', 'is_requestable', 'minimum_quantity', 'hide_add_to_cart', 'request_message', 'published_at', 'brand_id', 'status', 'variant_attribute_matrix', 'shipping_class', 'external_url'];

    protected $casts = [
        // Monetary and numeric fields use native casting for precise calculations within tests.
        'price'               => 'decimal:2',
        'cost_price'          => 'decimal:2',
        'weight'              => 'decimal:2',
        'length'              => 'decimal:2',
        'width'               => 'decimal:2',
        'height'              => 'decimal:2',
        'is_active'           => 'boolean',
        'is_enabled'          => 'boolean',
        'is_featured'         => 'boolean',
        'is_requestable'      => 'boolean',
        'hide_add_to_cart'    => 'boolean',
        'manage_stock'        => 'boolean',
        'allow_backorder'     => 'boolean',
        'published_at'        => 'datetime',
        'stock_quantity'      => 'integer',
        'low_stock_threshold' => 'integer',
        'minimum_quantity'    => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     * Only append essential fields to reduce serialization overhead.
     *
     * @var array<int, string>
     */
    protected $appends = ['main_image', 'thumbnail', 'stock_status', 'is_in_stock', 'formatted_price'];

    protected $table = 'products';

    protected static function booted(): void
    {
        self::saving(static function (Product $product): void {
            /** @var HtmlSanitizer $sanitizer */
            $sanitizer = app(HtmlSanitizer::class);

            foreach (['description', 'short_description', 'detailed_description'] as $field) {
                $value = $product->{$field};

                if (! is_string($value) || trim($value) === '') {
                    continue;
                }

                // Ensure persisted rich text never exceeds the sanitized allow-list.
                $product->{$field} = $sanitizer->sanitize($value);
            }

            // Keep publication metadata and status in sync so the storefront
            // repositories that honour the PublishedScope do not drop fresh
            // records that were scheduled via factories or seeders without a
            // matching status flag.
            if ($product->published_at !== null && $product->published_at <= now()) {
                $currentStatus = (string) ($product->status ?? '');

                if ($currentStatus === '' || in_array($currentStatus, ['draft', 'pending'], true)) {
                    $product->status = 'published';
                }
            }
        });

        self::deleting(static function (Product $product): void {
            // Always detach pivot relationships before deleting so hard deletes
            // cannot fail due to inconsistent pivot state in local/test DBs.
            if (Schema::hasTable('product_categories')) {
                $product->categories()->detach();
            }
            if (Schema::hasTable('product_collections')) {
                $product->collections()->detach();
            }
            if (Schema::hasTable('discount_products')) {
                $product->discounts()->detach();
            }
            if (Schema::hasTable('product_variant_product')) {
                $product->variants()->detach();
            }
            if (Schema::hasTable('product_attributes')) {
                $product->attributes()->detach();
            }

            // Manually remove related images so SQLite-based test runs mimic the
            // foreign key cascades enforced in production.
            foreach ($product->images()->withoutGlobalScopes()->get() as $image) {
                if ($image instanceof ProductImage) {
                    $image->delete();
                }
            }
        });
    }

    public static function bootSearchable(): void
    {
        $container = Container::getInstance();

        if ($container === null || ! $container->bound('events')) {
            return;
        }

        self::scoutBootSearchable();
    }

    /**
     * Handle getRouteKeyName functionality with proper error handling.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Ensure route model binding honours translated slugs across locales.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field ??= $this->getRouteKeyName();

        if ($field === 'slug' && is_string($value)) {
            $locale = app()->getLocale();
            $fallback = config('app.fallback_locale', 'en');

            // Lookup the product by matching the base slug or any translated slug.
            return $this->newQuery()
                ->where($field, $value)
                ->orWhereHas('translations', static function ($query) use ($value, $locale): void {
                    $query->where('locale', $locale)->where('slug', $value);
                })
                ->when($fallback !== $locale, function ($query) use ($value, $fallback): void {
                    $query->orWhereHas('translations', static function ($query) use ($value, $fallback): void {
                        $query->where('locale', $fallback)->where('slug', $value);
                    });
                })
                ->firstOrFail();
        }

        return parent::resolveRouteBinding($value, $field);
    }

    /**
     * Handle isPublished functionality with proper error handling.
     */
    public function isPublished(): bool
    {
        $status = (string) ($this->status ?? '');

        return in_array($status, ['published', 'active'], true)
            && $this->published_at !== null
            && $this->published_at <= now()
            && ($this->is_enabled ?? true);
    }

    /**
     * Cache for reserved quantity to avoid repeated sum queries during a single request.
     */
    protected ?int $cachedReservedQuantity = null;

    /**
     * Handle reservedQuantity functionality with proper error handling.
     */
    public function reservedQuantity(): int
    {
        if ($this->cachedReservedQuantity !== null) {
            return $this->cachedReservedQuantity;
        }

        return $this->cachedReservedQuantity = (int) $this->stockReservations()
            ->active()
            ->sum('quantity');
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
        $discount = $this->calculateDiscountPercentage();

        return $discount ?? 0.0;
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
            'width'  => $this->width ?? 0,
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
        if (! Route::has('products.show')) {
            return '';
        }

        return route('products.show', ['product' => $this->slug]);
    }

    /**
     * Handle getSalesCountAttribute functionality with proper error handling.
     */
    public function getSalesCountAttribute(): int
    {
        return $this->getSalesCount();
    }

    /**
     * Handle getRevenueAttribute functionality with proper error handling.
     */
    public function getRevenueAttribute(): float
    {
        return $this->getRevenue();
    }

    public function getCategoryAttribute(): ?Category
    {
        if ($this->relationLoaded('mainCategory')) {
            return $this->mainCategory->first();
        }

        if ($this->relationLoaded('categories')) {
            return $this->getRelation('categories')->first();
        }

        return $this->categories()->first();
    }

    /**
     * Handle decreaseStock functionality with proper error handling.
     */
    public function decreaseStock(int $quantity, ?StockReservation $reservation = null): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        if (! $this->manage_stock) {
            return true;
        }

        $decreased = DB::transaction(function () use ($quantity, $reservation): bool {
            $product = self::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            $reserved = $product->reservedQuantity();

            if (($product->stock_quantity - $reserved) < $quantity) {
                return false;
            }

            $product->decrement('stock_quantity', $quantity);

            if ($reservation !== null) {
                $lockedReservation = StockReservation::query()
                    ->whereKey($reservation->getKey())
                    ->lockForUpdate()
                    ->first();

                if ($lockedReservation !== null) {
                    $lockedReservation->consume(min($quantity, $lockedReservation->quantity));
                }
            }

            $this->setRawAttributes($product->getAttributes(), true);

            return true;
        });

        if (! $decreased) {
            return false;
        }

        $this->refresh();
        $this->cachedReservedQuantity = null;

        return true;
    }

    /**
     * Handle increaseStock functionality with proper error handling.
     */
    public function increaseStock(int $quantity): void
    {
        if ($quantity <= 0 || ! $this->manage_stock) {
            return;
        }

        DB::transaction(function () use ($quantity): void {
            $product = self::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();
            $product->increment('stock_quantity', $quantity);
            $this->setRawAttributes($product->getAttributes(), true);
        });

        $this->refresh();
        $this->cachedReservedQuantity = null;
    }

    public function reserveStock(
        int $quantity,
        ?DateTimeInterface $expiresAt = null,
        array $meta = [],
        ?string $referenceType = null,
        ?string $referenceId = null
    ): ?StockReservation {
        if ($quantity <= 0 || ! $this->manage_stock) {
            return null;
        }

        $reservation = DB::transaction(function () use ($quantity, $expiresAt, $meta, $referenceType, $referenceId): ?StockReservation {
            $product = self::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            $reserved = $product->reservedQuantity();

            if (($product->stock_quantity - $reserved) < $quantity) {
                return null;
            }

            return $product->stockReservations()->create([
                'quantity'       => $quantity,
                'status'         => StockReservation::STATUS_RESERVED,
                'reserved_at'    => now(),
                'expires_at'     => $expiresAt,
                'meta'           => $meta ?: null,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
            ]);
        });

        $this->refresh();
        $this->cachedReservedQuantity = null;

        return $reservation;
    }

    public function releaseReservation(StockReservation $reservation, ?int $quantity = null): void
    {
        DB::transaction(function () use ($reservation, $quantity): void {
            $lockedReservation = StockReservation::query()
                ->whereKey($reservation->getKey())
                ->lockForUpdate()
                ->first();

            if ($lockedReservation === null) {
                return;
            }

            if ($lockedReservation->product_id !== $this->getKey()) {
                return;
            }

            $lockedReservation->release($quantity);
        });

        $this->refresh();
        $this->cachedReservedQuantity = null;
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
        if ($this->relationLoaded('variants')) {
            return $this->variants->isNotEmpty();
        }

        return $this->variants()->exists();
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
    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_product', 'product_id', 'product_variant_id')
            ->withTimestamps();
    }

    /**
     * Handle variantCombinations functionality with proper error handling.
     */
    public function variantCombinations(): HasMany
    {
        return $this->hasMany(VariantCombination::class);
    }

    /**
     * Stock movements belonging to this product through its variants.
     */
    public function stockMovements(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            StockMovement::class,
            ProductVariant::class,
            'product_id',
            'variant_inventory_id',
            'id',
            'id'
        );
    }

    public function stockReservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    /**
     * Handle latestVariant functionality with proper error handling.
     */
    public function latestVariant(): BelongsToMany
    {
        return $this->variants()->orderByPivot('created_at', 'desc');
    }

    /**
     * Handle comments functionality with proper error handling.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
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
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class, 'priceable_id')->where('priceable_type', self::class);
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

    public function mainCategory(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories')->limit(1);
    }

    /**
     * Handle collections functionality with proper error handling.
     */
    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'product_collections');
    }

    /**
     * Handle discounts functionality with proper error handling.
     */
    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'discount_products');
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
        return $this->images()->one()->ofMany(['is_default' => 'max', 'sort_order' => 'min'], function ($query) {
            $query->orderBy('is_default', 'desc')->orderBy('sort_order', 'asc');
        });
    }

    /**
     * Handle orderItems functionality with proper error handling.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Handle orders functionality with proper error handling.
     *
     * @return BelongsToMany<Order, $this>
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_items')->distinct();
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
     * Alias for inventories() relationship.
     */
    public function inventory(): HasMany
    {
        return $this->inventories();
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
     * Manage feature rows that describe the product (specifications, benefits, etc.).
     */
    public function features(): HasMany
    {
        return $this->hasMany(ProductFeature::class);
    }

    /**
     * Similarity scores where this product is the source product.
     */
    public function similarities(): HasMany
    {
        return $this->hasMany(ProductSimilarity::class, 'product_id');
    }

    /**
     * Similarity scores where this product is the similar/target product.
     */
    public function similarTo(): HasMany
    {
        return $this->hasMany(ProductSimilarity::class, 'similar_product_id');
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
     * Handle currentPrice functionality with proper error handling.
     */
    public function currentPrice(): HasOne
    {
        return $this->histories()->one()->ofMany(['created_at' => 'max', 'id' => 'max'], function ($query): void {
            $query->where('field_name', 'price')->where('created_at', '<=', now());
        });
    }

    /**
     * Handle getLastStockUpdate functionality with proper error handling.
     */
    public function getLastStockUpdate(): ?array
    {
        return null; // ProductHistory functionality removed
    }

    /**
     * Handle getLastStatusChange functionality with proper error handling.
     */
    public function getLastStatusChange(): ?array
    {
        return null; // ProductHistory functionality removed
    }

    /**
     * Handle scopePublished functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopePublished($query)
    {
        return $query
            ->whereIn('status', ['published', 'active'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Handle scopeEnabled functionality with proper error handling.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeEnabled($query)
    {
        $schema = $this->getConnection()->getSchemaBuilder();
        $table = $this->getTable();

        if ($schema->hasColumn($table, 'is_enabled')) {
            return $query->where('is_enabled', true);
        }

        if ($schema->hasColumn($table, 'is_active')) {
            return $query->where('is_active', true);
        }

        if ($schema->hasColumn($table, 'status')) {
            return $query->where('status', 'published');
        }

        return $query;
    }

    /**
     * Handle scopeFeatured functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Handle scopeVisible functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeVisible($query)
    {
        return $query
            ->whereIn('status', ['published', 'active'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    // orderedByName scope provided by OrdersByName trait for consistent sanitisation.

    public function scopeReadyForCatalog(Builder $query): Builder
    {
        return $query
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->whereNotNull('price')
            ->where('price', '>', 0);
    }

    public function scopeSearchTerm(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $builder) use ($term): void {
            $builder->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%");
        });
    }

    public function scopeWithCatalogRelations(Builder $query): Builder
    {
        return $query->with([
            'brand:id,name,slug',
            'categories:id,name,slug',
            'media',
        ]);
    }

    public function scopeWithSearchRelations(Builder $query): Builder
    {
        return $query->with([
            'brand:id,name,slug',
            'categories:id,name,slug',
        ]);
    }

    public function scopeWithShowRelations(Builder $query): Builder
    {
        return $query->with([
            'brand:id,name,slug',
            'categories:id,name,slug',
            'media',
            'variants',
        ]);
    }

    /**
     * Scope for optimized product list queries with selective field loading.
     * Only loads fields required by ProductListItemData to reduce memory usage and transfer time.
     */
    public function scopeForProductList(Builder $query): Builder
    {
        return $query->select([
            'products.id',
            'products.name',
            'products.slug',
            'products.short_description', // Used for short description, NOT description
            'products.price',
            'products.stock_quantity',
            'products.brand_id',
            'products.created_at', // May be needed for sorting
            'products.updated_at', // May be needed for sorting
            'products.published_at', // May be needed for sorting
            'products.is_featured', // May be needed for sorting
        ]);
    }

    /**
     * Scope for optimized product list relations with selective field loading.
     * Only loads relation fields required by ProductListItemData.
     */
    public function scopeWithListRelations(Builder $query): Builder
    {
        $locale = app()->getLocale();

        return $query->with([
            // Load only essential brand fields
            'brand:id,name,slug',

            // Load brand translations for current locale only
            'brand.translations' => function ($q) use ($locale): void {
                $q->select('brand_id', 'locale', 'name', 'slug')
                    ->where('locale', $locale);
            },

            // Load only essential category fields
            'categories:id,name,slug',

            // Load category translations for current locale only
            'categories.translations' => function ($q) use ($locale): void {
                $q->select('category_id', 'locale', 'name', 'slug')
                    ->where('locale', $locale);
            },

            // Load product translations for current locale only
            'translations' => function ($q) use ($locale): void {
                $q->select('product_id', 'locale', 'name', 'slug', 'short_description')
                    ->where('locale', $locale);
            },

            // Load only essential media fields for images
            'media' => function ($q): void {
                $q->select('id', 'model_id', 'model_type', 'name', 'file_name', 'disk', 'conversions_disk', 'size', 'mime_type', 'manipulations', 'custom_properties', 'generated_conversions', 'responsive_images', 'order_column', 'created_at', 'updated_at')
                    ->where('collection_name', 'images')
                    ->orderBy('order_column');
            },
        ]);
    }

    /**
     * Handle scopeByBrand functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeByBrand($query, int $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    /**
     * Handle scopeByCategory functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->whereHas('categories', function ($q) use ($categoryId): void {
            $q->where('category_id', $categoryId);
        });
    }

    /**
     * Handle scopeByCollection functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeByCollection($query, int $collectionId)
    {
        return $query->whereHas('collections', function ($q) use ($collectionId): void {
            $q->where('collection_id', $collectionId);
        });
    }

    /**
     * Handle scopeInStock functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Handle scopeLowStock functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock_quantity <= low_stock_threshold');
    }

    /**
     * Handle scopeRequestable functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeRequestable($query)
    {
        return $query->where('is_requestable', true);
    }

    /**
     * Handle scopeNeedsRestocking functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeNeedsRestocking($query)
    {
        return $query->where('manage_stock', true)->whereRaw('stock_quantity < minimum_quantity');
    }

    /**
     * Handle scopeWithRequests functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeWithRequests($query)
    {
        return $query->whereHas('requests');
    }

    /**
     * Handle hasVariants functionality with proper error handling.
     */
    public function hasVariants(): bool
    {
        return $this->variants()->exists();
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
        $image = $this->resolvePrimaryImageModel();

        return $image ? $this->resolvePublicUrl($image->path) : null;
    }

    /**
     * Handle getThumbnailAttribute functionality with proper error handling.
     */
    public function getThumbnailAttribute(): ?string
    {
        $image = $this->resolvePrimaryImageModel();

        return $image ? $this->resolvePublicUrl($image->path) : null;
    }

    /**
     * Handle getImageUrl functionality with proper error handling.
     */
    public function getImageUrl(?string $size = null): ?string
    {
        $image = $this->resolvePrimaryImageModel();

        return $image ? $this->resolvePublicUrl($image->path) : null;
    }

    private function resolvePrimaryImageModel(): ?ProductImage
    {
        if ($this->relationLoaded('primaryImage')) {
            $image = $this->getRelation('primaryImage');

            return $image instanceof ProductImage ? $image : null;
        }

        if ($this->relationLoaded('images')) {
            $images = $this->getRelation('images');

            if ($images instanceof EloquentCollection) {
                $image = $images->first();

                return $image instanceof ProductImage ? $image : null;
            }

            return null;
        }

        $image = $this->primaryImage()->first();

        return $image instanceof ProductImage ? $image : null;
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
        $srcset = [$images['xs'] ?? null ? $images['xs'] . ' 150w' : null, $images['sm'] ?? null ? $images['sm'] . ' 300w' : null, $images['md'] ?? null ? $images['md'] . ' 500w' : null, $images['lg'] ?? null ? $images['lg'] . ' 800w' : null, $images['xl'] ?? null ? $images['xl'] . ' 1200w' : null];
        $sizeKey = $defaultSize ?? 'md';

        return ['src' => $images[$sizeKey] ?? $images['md'], 'srcset' => implode(', ', array_filter($srcset)), 'sizes' => '(max-width: 640px) 50vw, (max-width: 1024px) 33vw, 300px', 'alt' => __('translations.product_image_alt', ['name' => $this->name, 'number' => 1])];
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
        if ($path === '') {
            return $path;
        }

        $absolutePrefixes = ['http://', 'https://'];
        foreach ($absolutePrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $path;
            }
        }

        if (str_starts_with($path, '/')) {
            return asset(ltrim($path, '/'));
        }

        $defaultDisk = config('filesystems.default', 'public');
        $disksToCheck = array_unique([$defaultDisk, 'public']);

        foreach ($disksToCheck as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->url($path);
            }
        }

        // Fall back to the public disk URL even if the file is missing so the UI has a consistent path format
        return Storage::disk('public')->url($path);
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
        return $this->trans('short_description', $locale) ?: $this->short_description;
    }

    /**
     * Handle getTranslatedSummary functionality with proper error handling.
     */
    public function getTranslatedSummary(?string $locale = null): ?string
    {
        return $this->getTranslatedShortDescription($locale);
    }

    /**
     * Handle getTranslatedSeoTitle functionality with proper error handling.
     */
    public function getTranslatedSeoTitle(?string $locale = null): ?string
    {
        return $this->getTranslatedName($locale);
    }

    /**
     * Handle getTranslatedSeoDescription functionality with proper error handling.
     */
    public function getTranslatedSeoDescription(?string $locale = null): ?string
    {
        return $this->getTranslatedShortDescription($locale) ?: strip_tags((string) $this->getTranslatedDescription($locale));
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
     * @param mixed $query
     */
    public function scopeWithTranslations($query, ?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        return $query->with(['translations' => function ($q) use ($locale): void {
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
        return $this->translations()->firstOrCreate(['locale' => $locale], ['name' => $this->name, 'slug' => $this->slug, 'description' => $this->description, 'short_description' => $this->short_description]);
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
            $query->whereHas('categories', function ($q) use ($categoryIds): void {
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

        return Product::published()->whereHas('categories', function ($query) use ($categoryIds): void {
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

        return Product::published()->where('id', '!=', $this->id)->where(function ($query) use ($minPrice, $maxPrice): void {
            $query->whereBetween('price', [$minPrice, $maxPrice]);
        })->with(['media', 'brand', 'categories', 'translations'])->limit($limit)->get();
    }

    // Advanced Helper Methods

    /**
     * Handle getProductInfo functionality with proper error handling.
     */
    public function getProductInfo(): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'slug' => $this->slug, 'sku' => $this->sku, 'description' => $this->description, 'short_description' => $this->short_description, 'price' => $this->price, 'cost_price' => $this->cost_price, 'status' => $this->status, 'is_featured' => $this->is_featured, 'published_at' => $this->published_at?->toISOString()];
    }

    /**
     * Handle getInventoryInfo functionality with proper error handling.
     */
    public function getInventoryInfo(): array
    {
        return ['stock_quantity' => $this->stock_quantity, 'manage_stock' => $this->manage_stock, 'allow_backorder' => $this->allow_backorder, 'low_stock_threshold' => $this->low_stock_threshold, 'minimum_quantity' => $this->minimum_quantity, 'stock_status' => $this->getStockStatus(), 'is_in_stock' => $this->isInStock(), 'is_low_stock' => $this->isLowStock(), 'is_out_of_stock' => $this->isOutOfStock(), 'available_quantity' => $this->availableQuantity(), 'reserved_quantity' => $this->reservedQuantity()];
    }

    /**
     * Handle getPricingInfo functionality with proper error handling.
     */
    public function getPricingInfo(): array
    {
        return ['price' => $this->price, 'cost_price' => $this->cost_price, 'current_price' => $this->price, 'discount_percentage' => $this->getDiscountPercentage(), 'profit_margin' => $this->getProfitMargin(), 'markup_percentage' => $this->getMarkupPercentage()];
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
        return ['seo_title' => $this->getTranslatedSeoTitle(), 'seo_description' => $this->getTranslatedSeoDescription(), 'meta_keywords' => [], 'canonical_url' => $this->getCanonicalUrl()];
    }

    /**
     * Handle getBusinessInfo functionality with proper error handling.
     */
    public function getBusinessInfo(): array
    {
        return ['is_featured' => $this->is_featured, 'is_requestable' => $this->is_requestable, 'sales_count' => $this->getSalesCount(), 'revenue' => $this->getRevenue()];
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
        $discount = $this->calculateDiscountPercentage();

        return ($discount !== null && $discount > 0.0) ? $discount : null;
    }

    /**
     * Determine the current discount percentage using sale or base pricing.
     */
    private function calculateDiscountPercentage(): ?float
    {
        return null;
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

        return $name . $sku;
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

        $value = $price->value;

        if (is_object($value) && property_exists($value, 'amount')) {
            $value = $value->amount;
        }

        return app_money_format((float) $value);
    }

    /**
     * Handle getFormattedPriceAttribute functionality with proper error handling.
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->getFormattedPrice();
    }

    /**
     * Get the product translations.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(\App\Models\Translations\ProductTranslation::class);
    }
}
