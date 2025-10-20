<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\StatusScope;
use App\Models\Scopes\TrackedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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
    use HasFactory, SoftDeletes;

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
            'stock' => 'integer',
            'reserved' => 'integer',
            'available' => 'integer',
            'reorder_point' => 'integer',
            'reorder_quantity' => 'integer',
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
            'low_stock' => 'Low Stock',
            'in_stock' => 'In Stock',
            default => 'Unknown',
        };
    }

    /**
     * Handle scopeInStock functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeInStock($query)
    {
        return $query->where('available', '>', 0);
    }

    /**
     * Handle scopeLowStock functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('available <= reorder_point');
    }

    /**
     * Handle scopeOutOfStock functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('available', '<=', 0);
    }

    /**
     * Handle scopeNeedsReorder functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeNeedsReorder($query)
    {
        return $query->whereRaw('available <= reorder_point');
    }

    /**
     * Handle scopeByWarehouse functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByWarehouse($query, string $warehouseCode)
    {
        return $query->where('warehouse_code', $warehouseCode);
    }

    /**
     * Reserve stock for an order.
     */
    public function reserveStock(
        int $quantity,
        ?\DateTimeInterface $expiresAt = null,
        array $meta = [],
        ?string $referenceType = null,
        ?string $referenceId = null
    ): bool {
        if ($quantity <= 0) {
            return false;
        }

        $created = DB::transaction(function () use ($quantity, $expiresAt, $meta, $referenceType, $referenceId) {
            $inventory = self::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            $activeReservations = StockReservation::query()
                ->where('variant_inventory_id', $inventory->getKey())
                ->active()
                ->lockForUpdate()
                ->get();

            $currentReserved = (int) $activeReservations->sum('quantity');
            $available = max(0, $inventory->stock - $currentReserved);

            if ($available < $quantity) {
                return false;
            }

            $inventory->stockReservations()->create([
                'quantity' => $quantity,
                'status' => StockReservation::STATUS_RESERVED,
                'reserved_at' => now(),
                'expires_at' => $expiresAt,
                'meta' => $meta ?: null,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);

            $inventory->forceFill([
                'reserved' => $currentReserved + $quantity,
                'available' => max(0, $inventory->stock - ($currentReserved + $quantity)),
            ])->save();

            $this->setRawAttributes($inventory->getAttributes(), true);

            return true;
        });

        if (! $created) {
            return false;
        }

        $this->refresh();

        return true;
    }

    /**
     * Release reserved stock.
     */
    public function releaseStock(int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        $released = DB::transaction(function () use ($quantity) {
            $inventory = self::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            $reservations = StockReservation::query()
                ->where('variant_inventory_id', $inventory->getKey())
                ->active()
                ->lockForUpdate()
                ->orderBy('reserved_at')
                ->get();

            $currentReserved = (int) $reservations->sum('quantity');

            if ($currentReserved < $quantity) {
                return false;
            }

            $remaining = $quantity;

            foreach ($reservations as $reservation) {
                if ($remaining <= 0) {
                    break;
                }

                if ($reservation->quantity <= $remaining) {
                    $amount = $reservation->quantity;
                    $reservation->release();
                    $remaining -= $amount;
                } else {
                    $reservation->release($remaining);
                    $remaining = 0;
                }
            }

            $updatedReserved = (int) StockReservation::query()
                ->where('variant_inventory_id', $inventory->getKey())
                ->active()
                ->lockForUpdate()
                ->get()
                ->sum('quantity');

            $inventory->forceFill([
                'reserved' => $updatedReserved,
                'available' => max(0, $inventory->stock - $updatedReserved),
            ])->save();

            $this->setRawAttributes($inventory->getAttributes(), true);

            return true;
        });

        if (! $released) {
            return false;
        }

        $this->refresh();

        return true;
    }

    /**
     * Add stock to inventory.
     */
    public function addStock(int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        $added = DB::transaction(function () use ($quantity) {
            $inventory = self::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            $currentReserved = (int) StockReservation::query()
                ->where('variant_inventory_id', $inventory->getKey())
                ->active()
                ->lockForUpdate()
                ->get()
                ->sum('quantity');

            $inventory->forceFill([
                'stock' => $inventory->stock + $quantity,
                'reserved' => $currentReserved,
                'available' => max(0, ($inventory->stock + $quantity) - $currentReserved),
                'last_restocked_at' => now(),
            ])->save();

            $this->setRawAttributes($inventory->getAttributes(), true);

            return true;
        });

        if (! $added) {
            return false;
        }

        $this->refresh();

        return true;
    }

    /**
     * Remove stock from inventory.
     */
    public function removeStock(int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        $removed = DB::transaction(function () use ($quantity) {
            $inventory = self::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            $currentReserved = (int) StockReservation::query()
                ->where('variant_inventory_id', $inventory->getKey())
                ->active()
                ->lockForUpdate()
                ->get()
                ->sum('quantity');

            if (($inventory->stock - $quantity) < $currentReserved) {
                return false;
            }

            $inventory->forceFill([
                'stock' => $inventory->stock - $quantity,
                'reserved' => $currentReserved,
                'available' => max(0, ($inventory->stock - $quantity) - $currentReserved),
            ])->save();

            $this->setRawAttributes($inventory->getAttributes(), true);

            return true;
        });

        if (! $removed) {
            return false;
        }

        $this->refresh();

        return true;
    }

    public function adjustStock(int $quantity, string $reason = 'manual_adjustment'): bool
    {
        return $quantity >= 0
            ? $this->addStock($quantity)
            : $this->removeStock(abs($quantity));
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
     * Update available stock calculation.
     */
    public function updateAvailableStock(): bool
    {
        $reserved = (int) $this->stockReservations()->active()->sum('quantity');
        $this->reserved = $reserved;
        $this->available = max(0, $this->stock - $reserved);

        return $this->save();
    }

    /**
     * Get stock status badge color.
     */
    public function getStockStatusColor(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'danger',
            'low_stock' => 'warning',
            'in_stock' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Get stock status label.
     */
    public function getStockStatusLabel(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'Out of Stock',
            'low_stock' => 'Low Stock',
            'in_stock' => 'In Stock',
            default => 'Unknown',
        };
    }
}
