<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * VariantStockHistory
 * 
 * Model for tracking stock changes of product variants with comprehensive history and analytics.
 */
final class VariantStockHistory extends Model
{
    use HasFactory;

    protected $table = 'variant_stock_history';

    protected $fillable = [
        'variant_id',
        'old_quantity',
        'new_quantity',
        'quantity_change',
        'change_type',
        'change_reason',
        'changed_by',
        'reference_type',
        'reference_id',
    ];

    protected function casts(): array
    {
        return [
            'old_quantity' => 'integer',
            'new_quantity' => 'integer',
            'quantity_change' => 'integer',
        ];
    }

    /**
     * Get the variant that owns the stock history.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Get the user who made the stock change.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Get the reference model (order, return, adjustment, etc.).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo('reference');
    }

    /**
     * Check if the stock change is an increase.
     */
    public function isIncrease(): bool
    {
        return $this->quantity_change > 0;
    }

    /**
     * Check if the stock change is a decrease.
     */
    public function isDecrease(): bool
    {
        return $this->quantity_change < 0;
    }

    /**
     * Get the absolute quantity change.
     */
    public function getAbsoluteChangeAttribute(): int
    {
        return abs($this->quantity_change);
    }

    /**
     * Scope to filter by change type.
     */
    public function scopeByChangeType($query, string $changeType)
    {
        return $query->where('change_type', $changeType);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get stock increases.
     */
    public function scopeIncreases($query)
    {
        return $query->where('quantity_change', '>', 0);
    }

    /**
     * Scope to get stock decreases.
     */
    public function scopeDecreases($query)
    {
        return $query->where('quantity_change', '<', 0);
    }

    /**
     * Scope to get recent stock changes.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to get changes by reference.
     */
    public function scopeByReference($query, string $referenceType, int $referenceId)
    {
        return $query->where('reference_type', $referenceType)
                    ->where('reference_id', $referenceId);
    }

    /**
     * Create a stock history record.
     */
    public static function recordStockChange(
        int $variantId,
        int $oldQuantity,
        int $newQuantity,
        string $changeType = 'adjustment',
        ?string $changeReason = null,
        ?int $changedBy = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): self {
        return static::create([
            'variant_id' => $variantId,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'quantity_change' => $newQuantity - $oldQuantity,
            'change_type' => $changeType,
            'change_reason' => $changeReason,
            'changed_by' => $changedBy,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);
    }
}
