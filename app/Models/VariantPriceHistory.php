<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * VariantPriceHistory
 * 
 * Model for tracking price changes of product variants with comprehensive history and analytics.
 */
final class VariantPriceHistory extends Model
{
    use HasFactory;

    protected $table = 'variant_price_history';

    protected $fillable = [
        'variant_id',
        'old_price',
        'new_price',
        'price_type',
        'change_reason',
        'changed_by',
        'effective_from',
        'effective_until',
    ];

    protected function casts(): array
    {
        return [
            'old_price' => 'decimal:4',
            'new_price' => 'decimal:4',
            'effective_from' => 'datetime',
            'effective_until' => 'datetime',
        ];
    }

    /**
     * Get the variant that owns the price history.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Get the user who made the price change.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Get the price change amount.
     */
    public function getChangeAmountAttribute(): float
    {
        return $this->new_price - $this->old_price;
    }

    /**
     * Get the price change percentage.
     */
    public function getChangePercentageAttribute(): float
    {
        if ($this->old_price <= 0) {
            return 0;
        }
        
        return (($this->new_price - $this->old_price) / $this->old_price) * 100;
    }

    /**
     * Check if the price change is an increase.
     */
    public function isIncrease(): bool
    {
        return $this->new_price > $this->old_price;
    }

    /**
     * Check if the price change is a decrease.
     */
    public function isDecrease(): bool
    {
        return $this->new_price < $this->old_price;
    }

    /**
     * Scope to filter by price type.
     */
    public function scopeByPriceType($query, string $priceType)
    {
        return $query->where('price_type', $priceType);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get price increases.
     */
    public function scopeIncreases($query)
    {
        return $query->whereRaw('new_price > old_price');
    }

    /**
     * Scope to get price decreases.
     */
    public function scopeDecreases($query)
    {
        return $query->whereRaw('new_price < old_price');
    }

    /**
     * Scope to get recent price changes.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Create a price history record.
     */
    public static function recordPriceChange(
        int $variantId,
        float $oldPrice,
        float $newPrice,
        string $priceType = 'regular',
        ?string $changeReason = null,
        ?int $changedBy = null,
        ?\DateTime $effectiveFrom = null,
        ?\DateTime $effectiveUntil = null
    ): self {
        return static::create([
            'variant_id' => $variantId,
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'price_type' => $priceType,
            'change_reason' => $changeReason,
            'changed_by' => $changedBy,
            'effective_from' => $effectiveFrom,
            'effective_until' => $effectiveUntil,
        ]);
    }
}
