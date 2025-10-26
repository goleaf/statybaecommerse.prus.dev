<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\StatusScope;
use App\Models\Scopes\TrackedScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * VariantInventory
 *
 * Eloquent model representing the VariantInventory entity for variant stock management.
 *
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $appends
 *
 * @method static \Illuminate\Database\Eloquent\Builder|VariantInventory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantInventory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantInventory query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class, TrackedScope::class, StatusScope::class])]
final class VariantInventory extends Model
{
    use HasFactory;
    use OrdersByName;
    use SoftDeletes;

    protected $table = 'variant_inventories';

    protected $fillable = [
        'variant_id',
        'location_id',
        'warehouse_code',
        'stock',
        'reserved',
        'available',
        'incoming',
        'threshold',
        'reorder_point',
        'reorder_quantity',
        'max_stock_level',
        'cost_per_unit',
        'supplier_id',
        'batch_number',
        'expiry_date',
        'status',
        'is_tracked',
        'notes',
        'last_restocked_at',
        'last_sold_at',
    ];

    protected function casts(): array
    {
        return [
            'stock'             => 'integer',
            'reserved'          => 'integer',
            'available'         => 'integer',
            'reorder_point'     => 'integer',
            'reorder_quantity'  => 'integer',
            'last_restocked_at' => 'datetime',
        ];
    }

    protected $appends = [
        'is_low_stock',
        'is_out_of_stock',
        'needs_reorder',
        'stock_status',
        'utilization_percentage',
    ];

    /**
     * Handle variant functionality with proper error handling.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Handle location functionality with proper error handling.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'supplier_id');
    }

    /**
     * Handle stockMovements functionality with proper error handling.
     */
    public function stockMovements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StockMovement::class, 'variant_inventory_id');
    }

    public function stockReservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    /**
     * Handle isLowStock functionality with proper error handling.
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->available <= $this->reorder_point;
    }

    /**
     * Handle isOutOfStock functionality with proper error handling.
     */
    public function getIsOutOfStockAttribute(): bool
    {
        return $this->available <= 0;
    }

    /**
     * Handle needsReorder functionality with proper error handling.
     */
    public function getNeedsReorderAttribute(): bool
    {
        return $this->available <= $this->reorder_point;
    }

    /**
     * Handle getStockStatusAttribute functionality with proper error handling.
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->is_out_of_stock) {
            return 'out_of_stock';
        }

        if ($this->is_low_stock) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * Handle getUtilizationPercentageAttribute functionality with proper error handling.
     */
    public function getUtilizationPercentageAttribute(): float
    {
        if ($this->stock <= 0) {
            return 0.0;
        }

        return ($this->reserved / $this->stock) * 100;
    }

    public function getAvailableStockAttribute(): int
    {
        return max(0, (int) $this->stock - (int) $this->reserved);
    }

    public function getStockValueAttribute(): float
    {
        return (float) $this->stock * (float) $this->cost_per_unit;
    }

    public function getReservedValueAttribute(): float
    {
        return (float) $this->reserved * (float) $this->cost_per_unit;
    }

    public function getStockStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'Out of Stock',
            'low_stock'    => 'Low Stock',
            'in_stock'     => 'In Stock',
            default        => 'Unknown',
        };
    }

    /**
     * Handle scopeInStock functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeInStock($query)
    {
        return $query->where('available', '>', 0);
    }

    /**
     * Handle scopeLowStock functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('available <= reorder_point');
    }

    /**
     * Handle scopeOutOfStock functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('available', '<=', 0);
    }

    /**
     * Handle scopeNeedsReorder functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeNeedsReorder($query)
    {
        return $query->whereRaw('available <= reorder_point');
    }

    /**
     * Handle scopeExpiringSoon functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        // Leverage a configurable window so callers can tighten the threshold if necessary.
        $cutoffDate = Carbon::now()->addDays($days);

        return $query
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $cutoffDate);
    }

    /**
     * Handle scopeByWarehouse functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeByWarehouse($query, string $warehouseCode)
    {
        return $query->where('warehouse_code', $warehouseCode);
    }

    /**
     * @return Builder<self>
     */
    private static function newUnlockedQuery(): Builder
    {
        return self::query()->withoutGlobalScopes([
            ActiveScope::class,
            EnabledScope::class,
            TrackedScope::class,
            StatusScope::class,
        ]);
    }

    /**
     * Reserve stock for an order.
     */
    public function reserveStock(
        int $quantity,
        ?DateTimeInterface $expiresAt = null,
        array $meta = [],
        ?string $referenceType = null,
        ?string $referenceId = null
    ): bool {
        if ($quantity <= 0) {
            return false;
        }

        $reserved = DB::transaction(function () use ($quantity): bool {
            // Use an atomic update with a stock guard to prevent overselling in concurrent requests.
            $affected = self::newUnlockedQuery()
                ->whereKey($this->getKey())
                ->whereRaw('(stock - reserved) >= ?', [$quantity])
                ->update([
                    'reserved'  => DB::raw('reserved + ' . (int) $quantity),
                    'available' => DB::raw('CASE WHEN (stock - reserved - ' . (int) $quantity . ') > 0 THEN (stock - reserved - ' . (int) $quantity . ') ELSE 0 END'),
                ]);

            if ($affected !== 1) {
                return false;
            }

            $this->refresh();

            return true;
        });

        return (bool) $reserved;
    }

    /**
     * Release reserved stock.
     */
    public function releaseStock(int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        $released = DB::transaction(function () use ($quantity): bool {
            // Protect against negative reservations by requiring the column to stay non-negative.
            $affected = self::newUnlockedQuery()
                ->whereKey($this->getKey())
                ->where('reserved', '>=', $quantity)
                ->update([
                    'reserved'  => DB::raw('reserved - ' . (int) $quantity),
                    'available' => DB::raw('CASE WHEN (stock - (reserved - ' . (int) $quantity . ')) > 0 THEN (stock - (reserved - ' . (int) $quantity . ')) ELSE 0 END'),
                ]);

            if ($affected !== 1) {
                return false;
            }

            $this->refresh();

            return true;
        });

        return (bool) $released;
    }

    /**
     * Add stock to inventory.
     */
    public function addStock(
        int $quantity,
        string $reason = 'restock',
        ?int $actorId = null,
        ?string $correlationId = null,
        ?string $reference = null,
        ?string $notes = null
    ): bool {
        if ($quantity <= 0) {
            return false;
        }

        $correlation = $this->resolveCorrelationId($correlationId);

        $added = DB::transaction(function () use ($quantity, $reason, $actorId, $correlation, $reference, $notes): bool {
            // Perform an atomic increment to keep the stock counter accurate under contention.
            $affected = self::newUnlockedQuery()
                ->whereKey($this->getKey())
                ->update([
                    'stock'             => DB::raw('stock + ' . (int) $quantity),
                    'available'         => DB::raw('CASE WHEN ((stock + ' . (int) $quantity . ') - reserved) > 0 THEN ((stock + ' . (int) $quantity . ') - reserved) ELSE 0 END'),
                    'last_restocked_at' => now(),
                ]);

            if ($affected !== 1) {
                return false;
            }

            $this->refresh();

            $this->recordStockMovement($quantity, 'in', $reason, $actorId, $correlation, $reference, $notes);

            return true;
        });

        return (bool) $added;
    }

    /**
     * Remove stock from inventory.
     */
    public function removeStock(
        int $quantity,
        string $reason = 'manual_adjustment',
        ?int $actorId = null,
        ?string $correlationId = null,
        ?string $reference = null,
        ?string $notes = null
    ): bool {
        if ($quantity <= 0) {
            return false;
        }

        $correlation = $this->resolveCorrelationId($correlationId);

        $removed = DB::transaction(function () use ($quantity, $reason, $actorId, $correlation, $reference, $notes): bool {
            // Combine both stock and reserved guards to avoid negative balances during heavy throughput.
            $affected = self::newUnlockedQuery()
                ->whereKey($this->getKey())
                ->where('stock', '>=', $quantity)
                ->whereRaw('(stock - ' . (int) $quantity . ') >= reserved')
                ->update([
                    'stock'     => DB::raw('stock - ' . (int) $quantity),
                    'available' => DB::raw('CASE WHEN (stock - ' . (int) $quantity . ' - reserved) > 0 THEN (stock - ' . (int) $quantity . ' - reserved) ELSE 0 END'),
                ]);

            if ($affected !== 1) {
                return false;
            }

            $this->refresh();

            $this->recordStockMovement($quantity, 'out', $reason, $actorId, $correlation, $reference, $notes);

            return true;
        });

        return (bool) $removed;
    }

    public function adjustStock(
        int $quantity,
        string $reason = 'manual_adjustment',
        ?int $actorId = null,
        ?string $correlationId = null,
        ?string $reference = null,
        ?string $notes = null
    ): bool {
        if ($quantity === 0) {
            // Treat a zero adjustment as a no-op for idempotent API usage.
            return true;
        }

        return $quantity >= 0
            ? $this->addStock($quantity, $reason, $actorId, $correlationId, $reference, $notes)
            : $this->removeStock(abs($quantity), $reason, $actorId, $correlationId, $reference, $notes);
    }

    public function reserve(int $quantity): bool
    {
        return $this->reserveStock($quantity);
    }

    public function unreserve(int $quantity): bool
    {
        return $this->releaseStock($quantity);
    }

    /**
     * Resolve a usable correlation id for audit events.
     */
    private function resolveCorrelationId(?string $correlationId = null): string
    {
        // Allow callers to supply their own identifier to support idempotent retries.
        return $correlationId !== null && $correlationId !== ''
            ? $correlationId
            : Str::uuid()->toString();
    }

    /**
     * Persist a stock movement record for traceability.
     */
    private function recordStockMovement(
        int $quantity,
        string $type,
        string $reason,
        ?int $actorId,
        string $correlationId,
        ?string $reference = null,
        ?string $notes = null
    ): void {
        if ($quantity <= 0) {
            return;
        }

        // Skip duplicate writes when a repeated call reuses the same correlation id.
        if ($this->stockMovements()->where('correlation_id', $correlationId)->exists()) {
            return;
        }

        $this->stockMovements()->create([
            'quantity'       => $quantity,
            'type'           => $type,
            'reason'         => $reason,
            'reference'      => $reference,
            'correlation_id' => $correlationId,
            'notes'          => $notes,
            'user_id'        => $actorId,
            'moved_at'       => now(),
        ]);
    }

    /**
     * Update available stock calculation.
     */
    public function updateAvailableStock(): bool
    {
        $this->available = max(0, (int) $this->stock - (int) $this->reserved);

        return $this->save();
    }

    /**
     * Get stock status badge color.
     */
    public function getStockStatusColor(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'danger',
            'low_stock'    => 'warning',
            'in_stock'     => 'success',
            default        => 'secondary',
        };
    }

    /**
     * Get stock status label.
     */
    public function getStockStatusLabel(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'Out of Stock',
            'low_stock'    => 'Low Stock',
            'in_stock'     => 'In Stock',
            default        => 'Unknown',
        };
    }
}
