<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\DateRangeScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;
/**
 * PriceListItem
 * 
 * Eloquent model representing the PriceListItem entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @property array $translatable
 * @method static \Illuminate\Database\Eloquent\Builder|PriceListItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PriceListItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PriceListItem query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, DateRangeScope::class])]
final class PriceListItem extends Model
{
    use HasFactory, HasTranslations;
    protected $table = 'price_list_items';
    protected $fillable = ['price_list_id', 'product_id', 'variant_id', 'net_amount', 'compare_amount', 'name', 'description', 'notes', 'is_active', 'priority', 'min_quantity', 'max_quantity', 'valid_from', 'valid_until'];
    public array $translatable = ['name', 'description', 'notes'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['net_amount' => 'decimal:2', 'compare_amount' => 'decimal:2', 'is_active' => 'boolean', 'priority' => 'integer', 'min_quantity' => 'integer', 'max_quantity' => 'integer', 'valid_from' => 'datetime', 'valid_until' => 'datetime'];
    }
    /**
     * Handle priceList functionality with proper error handling.
     * @return BelongsTo
     */
    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }
    /**
     * Handle product functionality with proper error handling.
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    /**
     * Handle variant functionality with proper error handling.
     * @return BelongsTo
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
    /**
     * Handle getDiscountPercentageAttribute functionality with proper error handling.
     * @return int|null
     */
    public function getDiscountPercentageAttribute(): ?int
    {
        if (!$this->compare_amount || $this->compare_amount <= $this->net_amount) {
            return null;
        }
        return (int) round(($this->compare_amount - $this->net_amount) / $this->compare_amount * 100);
    }
    /**
     * Handle getDisplayNameAttribute functionality with proper error handling.
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->name) {
            return $this->trans('name') ?: $this->name;
        }
        if ($this->variant) {
            return $this->variant->display_name;
        }
        if ($this->product) {
            return $this->product->trans('name') ?: $this->product->name;
        }
        return 'Price List Item #' . $this->id;
    }
    /**
     * Handle getEffectivePriceAttribute functionality with proper error handling.
     * @return float
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->net_amount ?? 0.0;
    }
    /**
     * Handle getSavingsAmountAttribute functionality with proper error handling.
     * @return float|null
     */
    public function getSavingsAmountAttribute(): ?float
    {
        if (!$this->compare_amount || $this->compare_amount <= $this->net_amount) {
            return null;
        }
        return $this->compare_amount - $this->net_amount;
    }
    /**
     * Handle isActive functionality with proper error handling.
     * @return bool
     */
    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        $now = now();
        if ($this->valid_from && $this->valid_from->gt($now)) {
            return false;
        }
        if ($this->valid_until && $this->valid_until->lt($now)) {
            return false;
        }
        return true;
    }
    /**
     * Handle isValidForQuantity functionality with proper error handling.
     * @param int $quantity
     * @return bool
     */
    public function isValidForQuantity(int $quantity): bool
    {
        if ($this->min_quantity && $quantity < $this->min_quantity) {
            return false;
        }
        if ($this->max_quantity && $quantity > $this->max_quantity) {
            return false;
        }
        return true;
    }
    // Scopes
    /**
     * Handle scopeActive functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    /**
     * Handle scopeValid functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeValid($query)
    {
        $now = now();
        return $query->where('is_active', true)->where(function ($q) use ($now) {
            $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
        });
    }
    /**
     * Handle scopeByPriority functionality with proper error handling.
     * @param mixed $query
     * @param string $direction
     */
    public function scopeByPriority($query, string $direction = 'asc')
    {
        return $query->orderBy('priority', $direction);
    }
    /**
     * Handle scopeForProduct functionality with proper error handling.
     * @param mixed $query
     * @param int $productId
     */
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }
    /**
     * Handle scopeForVariant functionality with proper error handling.
     * @param mixed $query
     * @param int $variantId
     */
    public function scopeForVariant($query, int $variantId)
    {
        return $query->where('variant_id', $variantId);
    }
    /**
     * Handle scopeInPriceRange functionality with proper error handling.
     * @param mixed $query
     * @param float $minPrice
     * @param float $maxPrice
     */
    public function scopeInPriceRange($query, float $minPrice, float $maxPrice)
    {
        return $query->whereBetween('net_amount', [$minPrice, $maxPrice]);
    }
    /**
     * Handle scopeWithDiscount functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeWithDiscount($query)
    {
        return $query->whereNotNull('compare_amount')->whereColumn('compare_amount', '>', 'net_amount');
    }
    // Translation methods
    /**
     * Handle getTranslatedName functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedName(?string $locale = null): ?string
    {
        return $this->trans('name', $locale) ?: $this->name;
    }
    /**
     * Handle getTranslatedDescription functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedDescription(?string $locale = null): ?string
    {
        return $this->trans('description', $locale) ?: $this->description;
    }
    /**
     * Handle getTranslatedNotes functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedNotes(?string $locale = null): ?string
    {
        return $this->trans('notes', $locale) ?: $this->notes;
    }
}