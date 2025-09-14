<?php

declare (strict_types=1);
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
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class, StatusScope::class])]
final class ProductVariant extends Model implements HasMedia
{
    use HasFactory, HasProductPricing, InteractsWithMedia, SoftDeletes;
    protected $table = 'product_variants';
    protected $fillable = [
        'product_id', 'sku', 'name', 'price', 'compare_price', 'cost_price', 
        'track_quantity', 'quantity', 'weight', 'length', 'width', 'height', 
        'is_enabled', 'position', 'barcode', 'status', 'size', 'size_unit', 
        'size_display', 'size_price_modifier', 'size_weight_modifier', 
        'variant_type', 'is_default_variant', 'variant_sku_suffix', 
        'track_inventory', 'allow_backorder', 'low_stock_threshold', 
        'variant_metadata'
    ];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2', 
            'compare_price' => 'decimal:2', 
            'cost_price' => 'decimal:2', 
            'weight' => 'decimal:3', 
            'length' => 'decimal:2', 
            'width' => 'decimal:2', 
            'height' => 'decimal:2', 
            'track_quantity' => 'boolean', 
            'quantity' => 'integer', 
            'is_enabled' => 'boolean', 
            'position' => 'integer',
            'size_price_modifier' => 'decimal:4',
            'size_weight_modifier' => 'decimal:4',
            'is_default_variant' => 'boolean',
            'track_inventory' => 'boolean',
            'allow_backorder' => 'boolean',
            'low_stock_threshold' => 'integer',
            'variant_metadata' => 'array'
        ];
    }
    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'display_name', 'profit_margin', 'stock', 'available_quantity', 
        'reserved_quantity', 'is_out_of_stock', 'final_price', 'size_display_name',
        'variant_sku', 'is_low_stock', 'needs_reorder', 'stock_status'
    ];
    /**
     * Handle product functionality with proper error handling.
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    /**
     * Handle reservedQuantity functionality with proper error handling.
     * @return int
     */
    public function reservedQuantity(): int
    {
        $variantId = Number::parseFloat($this->id);
        $sum = Number::parseFloat(DB::table('variant_inventories as vi')->where('vi.variant_id', $variantId)->sum('vi.reserved'));
        return max($sum, 0);
    }
    /**
     * Handle availableQuantity functionality with proper error handling.
     * @return int
     */
    public function availableQuantity(): int
    {
        $variantId = Number::parseFloat($this->id);
        $sum = Number::parseFloat(DB::table('variant_inventories as vi')->where('vi.variant_id', $variantId)->sum(DB::raw('CASE WHEN (vi.stock - vi.reserved) > 0 THEN (vi.stock - vi.reserved) ELSE 0 END')));
        return max($sum, 0);
    }
    /**
     * Handle isOutOfStock functionality with proper error handling.
     * @return bool
     */
    public function isOutOfStock(): bool
    {
        return $this->availableQuantity() < 1;
    }
    /**
     * Handle getStockAttribute functionality with proper error handling.
     * @return int
     */
    public function getStockAttribute(): int
    {
        return (int) ($this->quantity ?? 0);
    }
    /**
     * Handle prices functionality with proper error handling.
     * @return MorphMany
     */
    public function prices(): MorphMany
    {
        return $this->morphMany(Price::class, 'priceable');
    }
    /**
     * Handle attributes functionality with proper error handling.
     * @return BelongsToMany
     */
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_attributes', 'variant_id', 'attribute_value_id');
    }
    /**
     * Handle inventories functionality with proper error handling.
     * @return HasMany
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(VariantInventory::class, 'variant_id');
    }
    /**
     * Handle orderItems functionality with proper error handling.
     * @return HasMany
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'variant_id');
    }
    /**
     * Handle cartItems functionality with proper error handling.
     * @return HasMany
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'variant_id');
    }
    /**
     * Handle scopeEnabled functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
    /**
     * Handle scopeInStock functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }
    /**
     * Handle scopeByStatus functionality with proper error handling.
     * @param mixed $query
     * @param string $status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
    /**
     * Handle getDisplayNameAttribute functionality with proper error handling.
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->product->name . ' - ' . $this->sku;
    }
    /**
     * Handle getProfitMarginAttribute functionality with proper error handling.
     * @return float|null
     */
    public function getProfitMarginAttribute(): ?float
    {
        if (!$this->cost_price || $this->cost_price <= 0) {
            return null;
        }
        return ($this->price - $this->cost_price) / $this->price * 100;
    }
    /**
     * Handle registerMediaCollections functionality with proper error handling.
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }
    /**
     * Handle registerMediaConversions functionality with proper error handling.
     * @param Media|null $media
     * @return void
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(150)->height(150)->sharpen(10);
        $this->addMediaConversion('small')->width(300)->height(300)->sharpen(10);
    }

    /**
     * Handle images functionality with proper error handling.
     * @return HasMany
     */
    public function images(): HasMany
    {
        return $this->hasMany(VariantImage::class, 'variant_id');
    }

    /**
     * Handle primaryImage functionality with proper error handling.
     * @return HasOne
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(VariantImage::class, 'variant_id')->where('is_primary', true);
    }

    /**
     * Handle pricingRules functionality with proper error handling.
     * @return HasMany
     */
    public function pricingRules(): HasMany
    {
        return $this->hasMany(VariantPricingRule::class, 'product_id', 'product_id');
    }

    /**
     * Handle getFinalPriceAttribute functionality with proper error handling.
     * @return float
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
     * @return string
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
     * @return string
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
     * @return bool
     */
    public function getIsLowStockAttribute(): bool
    {
        if (!$this->track_inventory) {
            return false;
        }
        
        return $this->availableQuantity() <= $this->low_stock_threshold;
    }

    /**
     * Handle getNeedsReorderAttribute functionality with proper error handling.
     * @return bool
     */
    public function getNeedsReorderAttribute(): bool
    {
        if (!$this->track_inventory) {
            return false;
        }
        
        return $this->availableQuantity() <= $this->low_stock_threshold;
    }

    /**
     * Handle getStockStatusAttribute functionality with proper error handling.
     * @return string
     */
    public function getStockStatusAttribute(): string
    {
        if (!$this->track_inventory) {
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
     * @param mixed $query
     * @param string $size
     */
    public function scopeBySize($query, string $size)
    {
        return $query->where('size', $size);
    }

    /**
     * Handle scopeByVariantType functionality with proper error handling.
     * @param mixed $query
     * @param string $type
     */
    public function scopeByVariantType($query, string $type)
    {
        return $query->where('variant_type', $type);
    }

    /**
     * Handle scopeDefaultVariant functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeDefaultVariant($query)
    {
        return $query->where('is_default_variant', true);
    }


    /**
     * Handle scopeLowStock functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeLowStock($query)
    {
        return $query->where('track_inventory', true)
            ->whereRaw('quantity <= low_stock_threshold');
    }

    /**
     * Handle scopeOutOfStock functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('track_inventory', true)
            ->where('quantity', '<=', 0);
    }

    /**
     * Set as default variant for the product.
     * @return bool
     */
    public function setAsDefault(): bool
    {
        // Remove default status from other variants of the same product
        static::where('product_id', $this->product_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default_variant' => false]);
        
        // Set this variant as default
        $this->is_default_variant = true;
        
        return $this->save();
    }

    /**
     * Get variant attributes as key-value pairs.
     * @return array
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
     * @return string
     */
    public function getVariantDisplayName(): string
    {
        $name = $this->product->name;
        $attributes = $this->getVariantAttributes();
        
        if (!empty($attributes)) {
            $attributeStrings = [];
            foreach ($attributes as $key => $value) {
                $attributeStrings[] = ucfirst($key) . ': ' . $value;
            }
            $name .= ' (' . implode(', ', $attributeStrings) . ')';
        }
        
        return $name;
    }

    /**
     * Check if variant is available for purchase.
     * @return bool
     */
    public function isAvailableForPurchase(): bool
    {
        if (!$this->is_enabled) {
            return false;
        }
        
        if ($this->track_inventory && $this->availableQuantity() <= 0 && !$this->allow_backorder) {
            return false;
        }
        
        return true;
    }

    /**
     * Get variant weight with size modifier.
     * @return float
     */
    public function getFinalWeight(): float
    {
        $baseWeight = $this->weight ?? 0;
        $sizeModifier = $this->size_weight_modifier ?? 0;
        
        return max(0, $baseWeight + $sizeModifier);
    }
}