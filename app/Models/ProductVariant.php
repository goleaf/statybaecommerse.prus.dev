<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\TranslatableRecord;
use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\StatusScope;
use App\Services\Pricing\VariantPriceService;
use App\Traits\HasProductPricing;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * ProductVariant
 *
 * Eloquent model representing the ProductVariant entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $appends
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class, StatusScope::class])]
final class ProductVariant extends Model implements HasMedia, TranslatableRecord
{
    use HasFactory;
    use HasProductPricing;
    use HasTranslations;
    use InteractsWithMedia;
    use OrdersByName;
    use SoftDeletes;

    protected $table = 'product_variants';

    protected string $translationModel = \App\Models\Translations\ProductVariantTranslation::class;

    /**
     * @var array<int, string>
     */
    protected array $translatable = [
        'name',
        'description',
        'seo_title',
        'seo_description',
    ];

    protected $fillable = [
        'product_id', 'sku', 'name', 'variant_name_lt', 'variant_name_en',
        'description_lt', 'description_en', 'price', 'cost_price',
        'wholesale_price', 'member_price', 'promotional_price',
        'stock_quantity', 'reserved_quantity', 'available_quantity', 'sold_quantity',
        'weight', 'track_inventory', 'is_default', 'is_default_variant', 'is_enabled', 'barcode', 'attributes', 'variant_attribute_matrix',
        'is_on_sale', 'sale_start_date', 'sale_end_date', 'is_featured', 'is_new', 'is_bestseller',
        'seo_title_lt', 'seo_title_en', 'seo_description_lt', 'seo_description_en',
        'variant_combination_hash',
    ];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return [
            'price'                    => 'decimal:4',
            'cost_price'               => 'decimal:4',
            'wholesale_price'          => 'decimal:4',
            'member_price'             => 'decimal:4',
            'promotional_price'        => 'decimal:4',
            'weight'                   => 'decimal:2',
            'stock_quantity'           => 'integer',
            'reserved_quantity'        => 'integer',
            'available_quantity'       => 'integer',
            'sold_quantity'            => 'integer',
            'track_inventory'          => 'boolean',
            'is_default'               => 'boolean',
            'is_default_variant'       => 'boolean',
            'is_enabled'               => 'boolean',
            'is_on_sale'               => 'boolean',
            'is_featured'              => 'boolean',
            'is_new'                   => 'boolean',
            'is_bestseller'            => 'boolean',
            'sale_start_date'          => 'datetime',
            'sale_end_date'            => 'datetime',
            'attributes'               => 'array',
            'variant_attribute_matrix' => 'array',
        ];
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'display_name', 'profit_margin', 'stock', 'available_quantity',
        'reserved_quantity', 'is_out_of_stock',
    ];

    /**
     * Handle product functionality with proper error handling.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Handle reservedQuantity functionality with proper error handling.
     */
    public function reservedQuantity(): int
    {
        $variantId = (int) ($this->getKey() ?? 0);
        $inventoryQuery = DB::table('variant_inventories as vi')->where('vi.variant_id', $variantId);

        // When no inventory rows exist (common in lightweight tests), fall back to
        // the stock-based columns on the variant itself.
        if (! $inventoryQuery->exists()) {
            return max(0, (int) ($this->reserved_quantity ?? 0));
        }

        $sum = Number::parseFloat((string) $inventoryQuery->sum('vi.reserved'));

        return (int) max($sum, 0);
    }

    /**
     * Ensure appended `reserved_quantity` access mirrors the explicit helper logic.
     */
    public function getReservedQuantityAttribute(): int
    {
        // Delegate to the core calculator so template consumers see consistent numbers.
        return $this->reservedQuantity();
    }

    /**
     * Handle availableQuantity functionality with proper error handling.
     */
    public function availableQuantity(): int
    {
        $variantId = (int) ($this->getKey() ?? 0);
        $inventoryQuery = DB::table('variant_inventories as vi')->where('vi.variant_id', $variantId);

        // Align admin filters and tests with the stock/reserved columns when no
        // per-warehouse inventory rows are present.
        if (! $inventoryQuery->exists()) {
            $stock = (int) ($this->stock_quantity ?? 0);
            $reserved = (int) ($this->reserved_quantity ?? 0);

            return max(0, $stock - $reserved);
        }

        $sum = Number::parseFloat((string) $inventoryQuery->sum(DB::raw('CASE WHEN (vi.stock - vi.reserved) > 0 THEN (vi.stock - vi.reserved) ELSE 0 END')));

        return (int) max($sum, 0);
    }

    /**
     * Bridge the appended `available_quantity` attribute that Filament infolists expect.
     */
    public function getAvailableQuantityAttribute(): int
    {
        // Reuse the shared calculator so the accessor and explicit method stay perfectly aligned.
        return $this->availableQuantity();
    }

    /**
     * Handle isOutOfStock functionality with proper error handling.
     */
    public function isOutOfStock(): bool
    {
        return $this->availableQuantity() < 1;
    }

    /**
     * Provide the appended `is_out_of_stock` attribute expected by resource views.
     */
    public function getIsOutOfStockAttribute(): bool
    {
        // Route through the shared helper so boolean logic stays centralized.
        return $this->isOutOfStock();
    }

    /**
     * Handle getStockAttribute functionality with proper error handling.
     */
    public function getStockAttribute(): int
    {
        return (int) ($this->stock_quantity ?? 0);
    }

    /**
     * Handle prices functionality with proper error handling.
     */
    public function prices(): MorphMany
    {
        return $this->morphMany(Price::class, 'priceable');
    }

    /**
     * Handle attributes functionality with proper error handling.
     */
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_attributes', 'variant_id', 'attribute_value_id')
            ->withPivot('attribute_id')
            ->withTimestamps();
    }

    /**
     * Handle variantAttributeValues functionality with proper error handling.
     */
    public function variantAttributeValues(): HasMany
    {
        return $this->hasMany(VariantAttributeValue::class, 'variant_id');
    }

    /**
     * Provide the legacy values() relationship used by storefront filters.
     */
    public function values(): BelongsToMany
    {
        // Reuse the attribute pivot so attribute data remains consistent across accessors.
        return $this->attributes();
    }

    /**
     * Handle inventories functionality with proper error handling.
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(VariantInventory::class, 'variant_id');
    }

    /**
     * Handle orderItems functionality with proper error handling.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'variant_id');
    }

    /**
     * Handle cartItems functionality with proper error handling.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'variant_id');
    }

    /**
     * Handle scopeOrderedByName functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeOrderedByName(Builder $query, string $direction = 'asc'): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $query->orderBy('name', $direction);
    }

    /**
     * Handle scopeEnabled functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Handle scopeInStock functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Handle scopeByStatus functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Handle getDisplayNameAttribute functionality with proper error handling.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->product->name . ' - ' . $this->sku;
    }

    /**
     * Handle getProfitMarginAttribute functionality with proper error handling.
     */
    public function getProfitMarginAttribute(): ?float
    {
        if (! $this->cost_price || $this->cost_price <= 0) {
            return null;
        }

        return ($this->price - $this->cost_price) / $this->price * 100;
    }

    /**
     * Handle registerMediaCollections functionality with proper error handling.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Handle registerMediaConversions functionality with proper error handling.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(150)->height(150)->sharpen(10);
        $this->addMediaConversion('small')->width(300)->height(300)->sharpen(10);
    }

    /**
     * Handle images functionality with proper error handling.
     */
    public function images(): HasMany
    {
        return $this->hasMany(VariantImage::class, 'variant_id');
    }

    /**
     * Handle primaryImage functionality with proper error handling.
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(VariantImage::class, 'variant_id')->where('is_primary', true);
    }

    /**
     * Handle pricingRules functionality with proper error handling.
     */
    public function pricingRules(): HasMany
    {
        /**
         * @var HasMany<VariantPricingRule> $relation
         */
        $relation = $this->hasMany(VariantPricingRule::class, 'product_id', 'product_id');

        // Extend the relationship so it captures variant-specific overrides alongside product-level rules.
        return $relation->where(function (Builder $query): void {
            $query->where('product_id', $this->product_id)
                ->orWhere('product_variant_id', $this->getKey());
        });
    }

    /**
     * Handle getFinalPriceAttribute functionality with proper error handling.
     */
    public function getFinalPriceAttribute(): float
    {
        /** @var VariantPriceService $service */
        $service = app(VariantPriceService::class);

        // Defer to the dedicated pricing service so all contextual rules (price lists, currency, history)
        // are consistently honoured across the application.
        return $service->calculate($this)->finalPrice;
    }

    /**
     * Handle getSizeDisplayNameAttribute functionality with proper error handling.
     */
    public function getSizeDisplayNameAttribute(): string
    {
        if ($this->size_display) {
            return $this->size_display;
        }

        if ($this->size) {
            return $this->size . ($this->size_unit ? ' ' . $this->size_unit : '');
        }

        return '';
    }

    /**
     * Handle getVariantSkuAttribute functionality with proper error handling.
     */
    public function getVariantSkuAttribute(): string
    {
        if ($this->variant_sku_suffix) {
            return $this->sku . '-' . $this->variant_sku_suffix;
        }

        return $this->sku;
    }

    /**
     * Handle getIsLowStockAttribute functionality with proper error handling.
     */
    public function getIsLowStockAttribute(): bool
    {
        if (! $this->track_inventory) {
            return false;
        }

        return $this->availableQuantity() <= $this->low_stock_threshold;
    }

    /**
     * Handle getNeedsReorderAttribute functionality with proper error handling.
     */
    public function getNeedsReorderAttribute(): bool
    {
        if (! $this->track_inventory) {
            return false;
        }

        return $this->availableQuantity() <= $this->low_stock_threshold;
    }

    /**
     * Handle getStockStatusAttribute functionality with proper error handling.
     */
    public function getStockStatusAttribute(): string
    {
        if (! $this->track_inventory) {
            return 'not_tracked';
        }

        $available = $this->availableQuantity();

        if ($available <= 0) {
            return 'out_of_stock';
        }

        if ($available <= $this->low_stock_threshold) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * Handle scopeBySize functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeBySize($query, string $size)
    {
        return $query->where('size', $size);
    }

    /**
     * Handle scopeByVariantType functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeByVariantType($query, string $type)
    {
        return $query->where('variant_type', $type);
    }

    /**
     * Handle scopeDefaultVariant functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeDefaultVariant($query)
    {
        return $query->where('is_default_variant', true);
    }

    /**
     * Handle scopeLowStock functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeLowStock($query)
    {
        return $query->where('track_inventory', true)
            ->whereRaw('quantity <= low_stock_threshold');
    }

    /**
     * Handle scopeOutOfStock functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('track_inventory', true)
            ->where('quantity', '<=', 0);
    }

    // orderedByName scope provided by OrdersByName trait which now ensures a
    // sanitised ordering direction for every consumer of this model.

    /**
     * Set as default variant for the product.
     */
    public function setAsDefault(): bool
    {
        // Remove default status from other variants of the same product
        self::where('product_id', $this->product_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default_variant' => false]);

        // Set this variant as default
        $this->is_default_variant = true;

        return $this->save();
    }

    /**
     * Get variant attributes as key-value pairs.
     */
    public function getVariantAttributes(): array
    {
        $attributes = [];

        foreach ($this->attributes as $attributeValue) {
            $attributes[$attributeValue->attribute->name] = $attributeValue->value;
        }

        return $attributes;
    }

    /**
     * Get variant display name with attributes.
     */
    public function getVariantDisplayName(): string
    {
        $name = $this->product->name;
        $attributes = $this->getVariantAttributes();

        if ($attributes !== []) {
            $attributeStrings = [];
            foreach ($attributes as $key => $value) {
                $attributeStrings[] = ucfirst((string) $key) . ': ' . $value;
            }
            $name .= ' (' . implode(', ', $attributeStrings) . ')';
        }

        return $name;
    }

    /**
     * Check if variant is available for purchase.
     */
    public function isAvailableForPurchase(): bool
    {
        if (! $this->is_enabled) {
            return false;
        }

        return ! ($this->track_inventory && $this->availableQuantity() <= 0 && ! $this->allow_backorder);
    }

    /**
     * Get variant weight with size modifier.
     */
    public function getFinalWeight(): float
    {
        $baseWeight = $this->weight ?? 0;
        $sizeModifier = $this->size_weight_modifier ?? 0;

        return max(0, $baseWeight + $sizeModifier);
    }

    /**
     * Get localized variant name.
     */
    public function getLocalizedName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        $translated = $this->trans('name', $locale);

        if (! filled($translated)) {
            $translated = match ($locale) {
                'lt'    => $this->variant_name_lt,
                'en'    => $this->variant_name_en,
                default => null,
            };
        }

        if (! filled($translated)) {
            $translated = $this->name ?? '';
        }

        return (string) $translated;
    }

    /**
     * Get localized variant description.
     */
    public function getLocalizedDescription(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        $translated = $this->trans('description', $locale);

        if (! filled($translated)) {
            $translated = match ($locale) {
                'lt'    => $this->description_lt,
                'en'    => $this->description_en,
                default => null,
            };
        }

        return $translated;
    }

    /**
     * Get localized SEO title.
     */
    public function getLocalizedSeoTitle(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        $translated = $this->trans('seo_title', $locale);

        if (! filled($translated)) {
            $translated = match ($locale) {
                'lt'    => $this->seo_title_lt,
                'en'    => $this->seo_title_en,
                default => null,
            };
        }

        return $translated;
    }

    /**
     * Get localized SEO description.
     */
    public function getLocalizedSeoDescription(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        $translated = $this->trans('seo_description', $locale);

        if (! filled($translated)) {
            $translated = match ($locale) {
                'lt'    => $this->seo_description_lt,
                'en'    => $this->seo_description_en,
                default => null,
            };
        }

        return $translated;
    }

    /**
     * Get the current effective price based on promotions and sales.
     */
    public function getCurrentPrice(): float
    {
        $basePrice = (float) $this->price;
        $promotionalPrice = $this->promotional_price !== null ? (float) $this->promotional_price : null;

        // Check if variant is on sale and within sale period
        if ($this->is_on_sale && $this->isCurrentlyOnSale()) {
            if ($promotionalPrice !== null && $promotionalPrice > 0) {
                return $promotionalPrice;
            }
        }

        return $basePrice;
    }

    /**
     * Check if variant is currently on sale.
     */
    public function isCurrentlyOnSale(): bool
    {
        if (! $this->is_on_sale) {
            return false;
        }

        $now = now();

        if ($this->sale_start_date && $now->isBefore($this->sale_start_date)) {
            return false;
        }

        return ! ($this->sale_end_date && $now->isAfter($this->sale_end_date));
    }

    /**
     * Get price for specific customer type.
     */
    public function getPriceForCustomerType(string $customerType = 'regular'): float
    {
        return match ($customerType) {
            'wholesale' => $this->wholesale_price ?: $this->price,
            'member'    => $this->member_price ?: $this->price,
            default     => $this->getCurrentPrice(),
        };
    }

    /**
     * Update available quantity.
     */
    public function updateAvailableQuantity(): bool
    {
        $this->available_quantity = max(0, $this->stock_quantity - $this->reserved_quantity);

        return $this->save();
    }

    /**
     * Check if variant is featured.
     */
    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    /**
     * Check if variant is new.
     */
    public function isNew(): bool
    {
        return $this->is_new;
    }

    /**
     * Check if variant is bestseller.
     */
    public function isBestseller(): bool
    {
        return $this->is_bestseller;
    }

    /**
     * Get variant combination hash.
     */
    public function getCombinationHash(): string
    {
        if ($this->variant_combination_hash) {
            return $this->variant_combination_hash;
        }

        // Generate hash from attributes
        $attributes = $this->variantAttributeValues()
            ->orderBy('attribute_name')
            ->get()
            ->pluck('attribute_value')
            ->implode('|');

        $this->variant_combination_hash = hash('sha256', $attributes);
        $this->save();

        return $this->variant_combination_hash;
    }

    /**
     * Scope for featured variants.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for new variants.
     */
    public function scopeNew($query)
    {
        return $query->where('is_new', true);
    }

    /**
     * Scope for bestseller variants.
     */
    public function scopeBestsellers($query)
    {
        return $query->where('is_bestseller', true);
    }

    /**
     * Scope for variants on sale.
     */
    public function scopeOnSale($query)
    {
        return $query->where('is_on_sale', true)
            ->where(function ($q): void {
                $q->whereNull('sale_start_date')
                    ->orWhere('sale_start_date', '<=', now());
            })
            ->where(function ($q): void {
                $q->whereNull('sale_end_date')
                    ->orWhere('sale_end_date', '>=', now());
            });
    }

    /**
     * Get the product variant translations.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(\App\Models\Translations\ProductVariantTranslation::class);
    }

    /**
     * Get translated field value for the specified locale.
     * Uses the eager-loaded translations relationship to avoid N+1 queries.
     */
    public function trans(string $field, ?string $locale = null): mixed
    {
        $locale ??= app()->getLocale();

        // If translations are loaded, use them to avoid additional queries
        if ($this->relationLoaded('translations')) {
            $translation = $this->translations->firstWhere('locale', $locale);
            if ($translation && isset($translation->{$field})) {
                return $translation->{$field};
            }
        }

        // Fallback to the base field value
        return $this->{$field} ?? null;
    }
}
