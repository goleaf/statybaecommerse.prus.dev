<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * VariantStockHistory
 *
 * Model for tracking stock changes of product variants with comprehensive history and analytics.
 *
 * @property int                             $id
 * @property int                             $variant_id
 * @property int                             $old_quantity
 * @property int                             $new_quantity
 * @property int                             $quantity_change
 * @property string                          $change_type
 * @property string|null                     $change_reason
 * @property int|null                        $changed_by
 * @property string|null                     $reference_type
 * @property int|null                        $reference_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int $absolute_change
 * @property-read \App\Models\ProductVariant $variant
 * @property-read \App\Models\User|null $changedBy
 * @property-read \Illuminate\Database\Eloquent\Model|Eloquent|null $reference
 *
 * @method static \Illuminate\Database\Eloquent\Builder|VariantStockHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantStockHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantStockHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantStockHistory byChangeType(string $changeType)
 * @method static \Illuminate\Database\Eloquent\Builder|VariantStockHistory inDateRange($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder|VariantStockHistory increases()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantStockHistory decreases()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantStockHistory recent(int $days = 30)
 * @method static \Illuminate\Database\Eloquent\Builder|VariantStockHistory byReference(string $referenceType, int $referenceId)
 *
 * @mixin \Eloquent
 */
final class VariantStockHistory extends Model
{
    /** @use HasFactory<\Database\Factories\VariantStockHistoryFactory> */
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
            'old_quantity'    => 'integer',
            'new_quantity'    => 'integer',
            'quantity_change' => 'integer',
        ];
    }

    /**
     * Get the variant that owns the stock history.
     *
     * @return BelongsTo<ProductVariant, $this>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Get the user who made the stock change.
     *
     * @return BelongsTo<User, $this>
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Get the reference model (order, return, adjustment, etc.).
     *
     * @return MorphTo<Model, $this>
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
     *
     * @param  Builder<VariantStockHistory> $query
     * @return Builder<VariantStockHistory>
     */
    public function scopeByChangeType(Builder $query, string $changeType): Builder
    {
        return $query->where('change_type', $changeType);
    }

    /**
     * Scope to filter by date range.
     *
     * @param  Builder<VariantStockHistory> $query
     * @return Builder<VariantStockHistory>
     */
    public function scopeInDateRange(Builder $query, mixed $startDate, mixed $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get stock increases.
     *
     * @param  Builder<VariantStockHistory> $query
     * @return Builder<VariantStockHistory>
     */
    public function scopeIncreases(Builder $query): Builder
    {
        return $query->where('quantity_change', '>', 0);
    }

    /**
     * Scope to get stock decreases.
     *
     * @param  Builder<VariantStockHistory> $query
     * @return Builder<VariantStockHistory>
     */
    public function scopeDecreases(Builder $query): Builder
    {
        return $query->where('quantity_change', '<', 0);
    }

    /**
     * Scope to get recent stock changes.
     *
     * @param  Builder<VariantStockHistory> $query
     * @return Builder<VariantStockHistory>
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to get changes by reference.
     *
     * @param  Builder<VariantStockHistory> $query
     * @return Builder<VariantStockHistory>
     */
    public function scopeByReference(Builder $query, string $referenceType, int $referenceId): Builder
    {
        return $query
            ->where('reference_type', $referenceType)
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
        return self::create([
            'variant_id'      => $variantId,
            'old_quantity'    => $oldQuantity,
            'new_quantity'    => $newQuantity,
            'quantity_change' => $newQuantity - $oldQuantity,
            'change_type'     => $changeType,
            'change_reason'   => $changeReason,
            'changed_by'      => $changedBy,
            'reference_type'  => $referenceType,
            'reference_id'    => $referenceId,
        ]);
    }
}
