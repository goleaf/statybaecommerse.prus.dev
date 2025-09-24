<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\StatusScope;
use App\Traits\HasProductPricing;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
final class ProductVariant extends Model implements HasMedia
{
    use HasFactory, HasProductPricing, InteractsWithMedia, SoftDeletes;

    protected $table = 'product_variants';

    protected $fillable = [
        'product_id', 'sku', 'name', 'variant_name_lt', 'variant_name_en',
        'description_lt', 'description_en', 'price', 'compare_price', 'cost_price',
        'wholesale_price', 'member_price', 'promotional_price',
        'stock_quantity', 'reserved_quantity', 'available_quantity', 'sold_quantity',
        'weight', 'track_inventory', 'is_default', 'is_enabled', 'barcode', 'attributes',
        'is_on_sale', 'sale_start_date', 'sale_end_date', 'is_featured', 'is_new', 'is_bestseller',
        'seo_title_lt', 'seo_title_en', 'seo_description_lt', 'seo_description_en',
        'views_count', 'clicks_count', 'conversion_rate', 'variant_combination_hash',
    ];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:4',
            'compare_price' => 'decimal:4',
            'cost_price' => 'decimal:4',
            'wholesale_price' => 'decimal:4',
            'member_price' => 'decimal:4',
            'promotional_price' => 'decimal:4',
            'weight' => 'decimal:2',
            'stock_quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'available_quantity' => 'integer',
            'sold_quantity' => 'integer',
            'track_inventory' => 'boolean',
            'is_default' => 'boolean',
            'is_enabled' => 'boolean',
            'is_on_sale' => 'boolean',
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'is_bestseller' => 'boolean',
            'sale_start_date' => 'datetime',
            'sale_end_date' => 'datetime',
            'views_count' => 'integer',
            'clicks_count' => 'integer',
            'conversion_rate' => 'decimal:4',
            'attributes' => 'array',
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
        $variantId = Number::parseFloat($this->id);
        $sum = Number::parseFloat(DB::table('variant_inventories as vi')->where('vi.variant_id', $variantId)->sum('vi.reserved'));

        return max($sum, 0);
    }

    /**
     * Handle availableQuantity functionality with proper error handling.
     */
    public function availableQuantity(): int
    {
        $variantId = Number::parseFloat($this->id);
        $sum = Number::parseFloat(DB::table('variant_inventories as vi')->where('vi.variant_id', $variantId)->sum(DB::raw('CASE WHEN (vi.stock - vi.reserved) > 0 THEN (vi.stock - vi.reserved) ELSE 0 END')));

        return max($sum, 0);
    }

    /**
     * Handle isOutOfStock functionality with proper error handling.
     */
    public function isOutOfStock(): bool
    {
        return $this->availableQuantity() < 1;
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
        return $this->belongsToMany(AttributeValue::class, 'product_variant_attributes', 'variant_id', 'attribute_value_id');
    }

    /**
     * Handle variantAttributeValues functionality with proper error handling.
     */
    public function variantAttributeValues(): HasMany
    {
        return $this->hasMany(VariantAttributeValue::class, 'variant_id');
    }

    /**
     * Handle priceHistory functionality with proper error handling.
     */
    public function priceHistory(): HasMany
    {
        return $this->hasMany(VariantPriceHistory::class, 'variant_id');
    }

    /**
     * Handle stockHistory functionality with proper error handling.
     */
    public function stockHistory(): HasMany
    {
        return $this->hasMany(VariantStockHistory::class, 'variant_id');
    }

    /**
     * Handle analytics functionality with proper error handling.
     */
    public function analytics(): HasMany
    {
        return $this->hasMany(VariantAnalytics::class, 'variant_id');
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
     * Handle scopeEnabled functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Handle scopeInStock functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Handle scopeByStatus functionality with proper error handling.
     *
     * @param  mixed  $query
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
        return $this->name ?: $this->product->name.' - '.$this->sku;
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
        return $this->hasMany(VariantPricingRule::class, 'product_id', 'product_id');
    }

    /**
     * Handle getFinalPriceAttribute functionality with proper error handling.
     */
    public function getFinalPriceAttribute(): float
    {
        $basePrice = $this->price;
        $sizeModifier = $this->size_price_modifier ?? 0;

        // Apply size-based pricing modifier
        $finalPrice = $basePrice + $sizeModifier;

        // Apply dynamic pricing rules
        $pricingRules = $this->pricingRules()->active()->orderedByPriority()->get();
        foreach ($pricingRules as $rule) {
            $modifier = $rule->calculatePriceModifier($this);
            $finalPrice += $modifier;
        }

        return max(0, $finalPrice);
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
            return $this->size.($this->size_unit ? ' '.$this->size_unit : '');
        }

        return '';
    }

    /**
     * Handle getVariantSkuAttribute functionality with proper error handling.
     */
    public function getVariantSkuAttribute(): string
    {
        if ($this->variant_sku_suffix) {
            return $this->sku.'-'.$this->variant_sku_suffix;
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
     * @param  mixed  $query
     */
    public function scopeBySize($query, string $size)
    {
        return $query->where('size', $size);
    }

    /**
     * Handle scopeByVariantType functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByVariantType($query, string $type)
    {
        return $query->where('variant_type', $type);
    }

    /**
     * Handle scopeDefaultVariant functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeDefaultVariant($query)
    {
        return $query->where('is_default_variant', true);
    }

    /**
     * Handle scopeLowStock functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeLowStock($query)
    {
        return $query->where('track_inventory', true)
            ->whereRaw('quantity <= low_stock_threshold');
    }

    /**
     * Handle scopeOutOfStock functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('track_inventory', true)
            ->where('quantity', '<=', 0);
    }

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

        if (! empty($attributes)) {
            $attributeStrings = [];
            foreach ($attributes as $key => $value) {
                $attributeStrings[] = ucfirst($key).': '.$value;
            }
            $name .= ' ('.implode(', ', $attributeStrings).')';
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

        if ($this->track_inventory && $this->availableQuantity() <= 0 && ! $this->allow_backorder) {
            return false;
        }

        return true;
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
        $locale = $locale ?: app()->getLocale();

        return match ($locale) {
            'lt' => $this->variant_name_lt ?: $this->name,
            'en' => $this->variant_name_en ?: $this->name,
            default => $this->name,
        };
    }

    /**
     * Get localized variant description.
     */
    public function getLocalizedDescription(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();

        return match ($locale) {
            'lt' => $this->description_lt,
            'en' => $this->description_en,
            default => null,
        };
    }

    /**
     * Get localized SEO title.
     */
    public function getLocalizedSeoTitle(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();

        return match ($locale) {
            'lt' => $this->seo_title_lt,
            'en' => $this->seo_title_en,
            default => null,
        };
    }

    /**
     * Get localized SEO description.
     */
    public function getLocalizedSeoDescription(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();

        return match ($locale) {
            'lt' => $this->seo_description_lt,
            'en' => $this->seo_description_en,
            default => null,
        };
    }

    /**
     * Get the current effective price based on promotions and sales.
     */
    public function getCurrentPrice(): float
    {
        // Check if variant is on sale and within sale period
        if ($this->is_on_sale && $this->isCurrentlyOnSale()) {
            if ($this->promotional_price && $this->promotional_price > 0) {
                return $this->promotional_price;
            }

            // Apply sale discount if no promotional price set
            if ($this->compare_price && $this->compare_price > $this->price) {
                return $this->price;
            }
        }

        return $this->price;
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

        if ($this->sale_end_date && $now->isAfter($this->sale_end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Get price for specific customer type.
     */
    public function getPriceForCustomerType(string $customerType = 'regular'): float
    {
        return match ($customerType) {
            'wholesale' => $this->wholesale_price ?: $this->price,
            'member' => $this->member_price ?: $this->price,
            default => $this->getCurrentPrice(),
        };
    }

    /**
     * Record a view for analytics.
     */
    public function recordView(): bool
    {
        $this->increment('views_count');

        // Record daily analytics
        $this->recordDailyAnalytics('views');

        return true;
    }

    /**
     * Record a click for analytics.
     */
    public function recordClick(): bool
    {
        $this->increment('clicks_count');

        // Record daily analytics
        $this->recordDailyAnalytics('clicks');

        return true;
    }

    /**
     * Record daily analytics data.
     */
    public function recordDailyAnalytics(string $metric, int $amount = 1): void
    {
        $today = now()->toDateString();

        VariantAnalytics::recordAnalytics($this->id, $today, [
            $metric => $amount,
        ]);
    }

    /**
     * Update conversion rate.
     */
    public function updateConversionRate(): bool
    {
        if ($this->views_count > 0) {
            $this->conversion_rate = ($this->sold_quantity / $this->views_count) * 100;

            return $this->save();
        }

        return false;
    }

    /**
     * Record price change in history.
     */
    public function recordPriceChange(
        float $oldPrice,
        ?string $changeReason = null,
        ?int $changedBy = null
    ): VariantPriceHistory {
        return VariantPriceHistory::recordPriceChange(
            $this->id,
            $oldPrice,
            $this->price,
            'regular',
            $changeReason,
            $changedBy
        );
    }

    /**
     * Record stock change in history.
     */
    public function recordStockChange(
        int $oldQuantity,
        string $changeType = 'adjustment',
        ?string $changeReason = null,
        ?int $changedBy = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): VariantStockHistory {
        return VariantStockHistory::recordStockChange(
            $this->id,
            $oldQuantity,
            $this->stock_quantity,
            $changeType,
            $changeReason,
            $changedBy,
            $referenceType,
            $referenceId
        );
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
            ->where(function ($q) {
                $q->whereNull('sale_start_date')
                    ->orWhere('sale_start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('sale_end_date')
                    ->orWhere('sale_end_date', '>=', now());
            });
    }

    /**
     * Scope for variants with high conversion rate.
     */
    public function scopeHighConverting($query, float $threshold = 5.0)
    {
        return $query->where('conversion_rate', '>=', $threshold);
    }

    /**
     * Scope for variants with high views.
     */
    public function scopePopular($query, int $threshold = 100)
    {
        return $query->where('views_count', '>=', $threshold);
    }
}
