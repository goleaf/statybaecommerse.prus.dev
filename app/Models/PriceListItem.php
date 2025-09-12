<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

final class PriceListItem extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'price_list_items';

    protected $fillable = [
        'price_list_id',
        'product_id',
        'variant_id',
        'net_amount',
        'compare_amount',
        'name',
        'description',
        'notes',
        'is_active',
        'priority',
        'min_quantity',
        'max_quantity',
        'valid_from',
        'valid_until',
    ];

    public array $translatable = [
        'name',
        'description',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'net_amount' => 'decimal:4',
            'compare_amount' => 'decimal:4',
            'is_active' => 'boolean',
            'priority' => 'integer',
            'min_quantity' => 'integer',
            'max_quantity' => 'integer',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
        ];
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function getDiscountPercentageAttribute(): ?int
    {
        if (!$this->compare_amount || $this->compare_amount <= $this->net_amount) {
            return null;
        }

        return (int) round((($this->compare_amount - $this->net_amount) / $this->compare_amount) * 100);
    }

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

    public function getEffectivePriceAttribute(): float
    {
        return $this->net_amount ?? 0.0;
    }

    public function getSavingsAmountAttribute(): ?float
    {
        if (!$this->compare_amount || $this->compare_amount <= $this->net_amount) {
            return null;
        }

        return $this->compare_amount - $this->net_amount;
    }

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
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_from')
                  ->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>=', $now);
            });
    }

    public function scopeByPriority($query, string $direction = 'asc')
    {
        return $query->orderBy('priority', $direction);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForVariant($query, int $variantId)
    {
        return $query->where('variant_id', $variantId);
    }

    public function scopeInPriceRange($query, float $minPrice, float $maxPrice)
    {
        return $query->whereBetween('net_amount', [$minPrice, $maxPrice]);
    }

    public function scopeWithDiscount($query)
    {
        return $query->whereNotNull('compare_amount')
            ->whereColumn('compare_amount', '>', 'net_amount');
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

    public function getTranslatedNotes(?string $locale = null): ?string
    {
        return $this->trans('notes', $locale) ?: $this->notes;
    }
}
