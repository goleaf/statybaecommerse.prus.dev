<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\TranslatableRecord;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\PublishedScope;
use App\Models\Scopes\VisibleScope;
use App\Observers\ProductObserver;
use App\Support\Html\HtmlSanitizer;
use App\Traits\HasProductPricing;
use App\Traits\HasTranslations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;
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
 * @property mixed  $fillable
 * @property mixed  $casts
 * @property mixed  $appends
 * @property mixed  $table
 * @property string $translationModel
 * @property array $translatable
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property string|null $short_description
 * @property string|null $description
 * @property string|null $sku
 * @property float|string|null $price
 * @property bool $is_featured
 * @property bool $is_visible
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property-read Brand|null $brand
 * @property-read string|null $thumbnail
 * @property-read string|null $main_image
 * @property-read int $sales_count
 * @property-read int $reviews_count
 * @property-read float $average_rating
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 *
 * @mixin \Eloquent
 */
#[ObservedBy([ProductObserver::class])]
#[ScopedBy([ActiveScope::class, PublishedScope::class, VisibleScope::class])]
final class Product extends Model implements HasMedia, TranslatableRecord
{
    use HasFactory, Searchable, SoftDeletes;
    use HasProductPricing;
    use HasTranslations;
    use InteractsWithMedia;
    use LogsActivity;

    public const SCOPE_COLUMN_HINTS = [
        'is_active'    => false,
        'is_visible'   => true,
        'is_enabled'   => true,
        'status'       => true,
        'published_at' => true,
    ];

    protected $fillable = ['name', 'slug', 'description', 'short_description', 'sku', 'barcode', 'price', 'compare_price', 'cost_price', 'sale_price', 'manage_stock', 'track_stock', 'allow_backorder', 'stock_quantity', 'low_stock_threshold', 'weight', 'length', 'width', 'height', 'is_active', 'is_visible', 'is_enabled', 'is_featured', 'is_requestable', 'requests_count', 'minimum_quantity', 'hide_add_to_cart', 'request_message', 'published_at', 'seo_title', 'seo_description', 'brand_id', 'status', 'type', 'video_url', 'metadata', 'variant_attribute_matrix', 'sort_order', 'tax_class', 'shipping_class', 'download_limit', 'download_expiry', 'external_url', 'button_text'];

    protected $casts = [
        // Monetary and numeric fields use native casting for precise calculations within tests.
        'price'               => 'decimal:2',
        'compare_price'       => 'decimal:2',
        'cost_price'          => 'decimal:2',
        'sale_price'          => 'decimal:2',
        'weight'              => 'decimal:2',
        'length'              => 'decimal:2',
        'width'               => 'decimal:2',
        'height'              => 'decimal:2',
        'is_active'           => 'boolean',
        'is_visible'          => 'boolean',
        'is_enabled'          => 'boolean',
        'is_featured'         => 'boolean',
        'is_requestable'      => 'boolean',
        'hide_add_to_cart'    => 'boolean',
        'manage_stock'        => 'boolean',
        'track_stock'         => 'boolean',
        'allow_backorder'     => 'boolean',
        'published_at'        => 'datetime',
        'stock_quantity'      => 'integer',
        'low_stock_threshold' => 'integer',
        'requests_count'      => 'integer',
        'minimum_quantity'    => 'integer',
        'sort_order'          => 'integer',
        'download_limit'      => 'integer',
        'download_expiry'     => 'integer',
        'metadata'            => 'array',
    ];

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

    protected static function booted(): void
    {
        self::saving(static function (Product $product): void {
            /** @var HtmlSanitizer $sanitizer */
            $sanitizer = app(HtmlSanitizer::class);

            foreach (['description', 'short_description'] as $field) {
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
    }

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
        return $this->is_visible && $this->published_at && $this->published_at <= now();
    }

    /**
     * Handle reservedQuantity functionality with proper error handling.
     */
    public function reservedQuantity(): int
    {
        return (int) $this->stockReservations()
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

    public function getCategoryAttribute(): ?Category
    {
        if (! $this->relationLoaded('categories')) {
            return null;
        }

        return $this->getRelation('categories')->first();
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

            $activeReservations = StockReservation::query()
                ->where('product_id', $product->getKey())
                ->active()
                ->lockForUpdate()
                ->get();

            $reserved = (int) $activeReservations->sum('quantity');

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
    }

    public function reserveStock(
        int $quantity,
        ?\DateTimeInterface $expiresAt = null,
        array $meta = [],
        ?string $referenceType = null,
        ?string $referenceId = null
    ): ?StockReservation {
        if ($quantity <= 0 || ! $this->manage_stock) {
            return null;
        }

        $reservation = DB::transaction(function () use ($quantity, $expiresAt, $meta, $referenceType, $referenceId): ?StockReservation {
            $product = self::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            $reserved = (int) StockReservation::query()
                ->where('product_id', $product->getKey())
                ->active()
                ->lockForUpdate()
                ->get()
                ->sum('quantity');

            if (($product->stock_quantity - $reserved) < $quantity) {
                return null;
            }

            return $product->stockReservations()->create([
                'quantity' => $quantity,
                'status' => StockReservation::STATUS_RESERVED,
                'reserved_at' => now(),
                'expires_at' => $expiresAt,
                'meta' => $meta ?: null,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);
        });

        $this->refresh();

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

    public function stockReservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
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
        return $this->reviews()->one()->ofMany(['created_at' => 'max'], function ($query): void {
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
        return $this->histories()->one()->ofMany(['created_at' => 'max'], function ($query): void {
            $query->where('field_name', 'price');
        });
    }

    /**
     * Handle latestStockUpdate functionality with proper error handling.
     */
    public function latestStockUpdate(): HasOne
    {
        return $this->histories()->one()->ofMany(['created_at' => 'max'], function ($query): void {
            $query->where('field_name', 'stock_quantity');
        });
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
     * @param mixed $query
     */
    public function scopePublished($query)
    {
        return $query->where('is_visible', true)->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
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
        return $query->where('is_visible', true);
    }

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
        return $query->where('requests_count', '>', 0);
    }

    /**
     * Handle getAverageRatingAttribute functionality with proper error handling.
     */
    public function getAverageRatingAttribute(): float
    {
        // When aggregate values are eager loaded (e.g. via loadAvg), prefer the hydrated
        // attributes so we do not run redundant queries when the value is already known.
        foreach (['average_rating', 'reviews_avg_rating', 'approved_reviews_avg_rating'] as $attribute) {
            if (array_key_exists($attribute, $this->attributes)) {
                $rating = $this->attributes[$attribute];

                return $rating !== null ? (float) $rating : 0.0;
            }
        }

        if ($this->relationLoaded('reviews')) {
            $rating = $this->getRelation('reviews')->avg('rating');

            return $rating !== null ? (float) $rating : 0.0;
        }

        $rating = $this->reviews()->approved()->avg('rating');

        return $rating ? (float) $rating : 0.0;
    }

    /**
     * Handle getReviewsCountAttribute functionality with proper error handling.
     */
    public function getReviewsCountAttribute(): int
    {
        // Respect any eager-loaded aggregate counts (loadCount / withCount) to avoid
        // re-querying the database when the information is already on the model.
        foreach (['reviews_count', 'approved_reviews_count'] as $attribute) {
            if (array_key_exists($attribute, $this->attributes)) {
                return (int) $this->attributes[$attribute];
            }
        }

        if ($this->relationLoaded('reviews')) {
            return $this->getRelation('reviews')->count();
        }

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
        $srcset = [$images['xs'] ?? null ? $images['xs'].' 150w' : null, $images['sm'] ?? null ? $images['sm'].' 300w' : null, $images['md'] ?? null ? $images['md'].' 500w' : null, $images['lg'] ?? null ? $images['lg'].' 800w' : null, $images['xl'] ?? null ? $images['xl'].' 1200w' : null];
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
        $discount = $this->calculateDiscountPercentage();

        return ($discount !== null && $discount > 0.0) ? $discount : null;
    }

    /**
     * Determine the current discount percentage using sale or base pricing.
     */
    private function calculateDiscountPercentage(): ?float
    {
        $comparePrice = $this->compare_price ?? $this->price;
        $currentPrice = $this->sale_price ?? $this->price;

        if ($comparePrice === null || $currentPrice === null) {
            return null;
        }

        $compare = (float) $comparePrice;
        $current = (float) $currentPrice;

        if ($compare <= 0.0 || $current <= 0.0 || $current >= $compare) {
            return 0.0;
        }

        return round((($compare - $current) / $compare) * 100, 2);
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
