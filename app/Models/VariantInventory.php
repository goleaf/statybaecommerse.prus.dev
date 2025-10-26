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
    use HasFactory;
    use OrdersByName {
        getNameColumn as protected resolveNameColumnForOrdering;
        scopeOrderedByName as protected scopeOrderedByNameFromTrait;
    }
    use SoftDeletes;

    /**
     * Order stock rows by the related variant SKU when leveraging the shared
     * OrdersByName scope.
     */
    protected string $nameColumn = 'sku';

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

        $reserved = DB::transaction(function () use ($quantity) {
            $inventory = self::newUnlockedQuery()
                ->whereKey($this->getKey())
                ->lockForUpdate()
                ->first();

            if (! $inventory instanceof self) {
                return false;
            }

            $currentReserved = (int) $inventory->reserved;
            $currentStock = (int) $inventory->stock;
            $available = max(0, $currentStock - $currentReserved);

            if ($available < $quantity) {
                return false;
            }

            $inventory->forceFill([
                'reserved'  => $currentReserved + $quantity,
                'available' => max(0, $currentStock - ($currentReserved + $quantity)),
            ])->save();

            $this->setRawAttributes($inventory->getAttributes(), true);

            return true;
        });

        if (! $reserved) {
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
            $inventory = self::newUnlockedQuery()
                ->whereKey($this->getKey())
                ->lockForUpdate()
                ->first();

            if (! $inventory instanceof self) {
                return false;
            }

            $currentReserved = (int) $inventory->reserved;

            if ($currentReserved < $quantity) {
                return false;
            }

            $inventory->forceFill([
                'reserved'  => $currentReserved - $quantity,
                'available' => max(0, ((int) $inventory->stock) - ($currentReserved - $quantity)),
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
            $inventory = self::newUnlockedQuery()
                ->whereKey($this->getKey())
                ->lockForUpdate()
                ->first();

            if (! $inventory instanceof self) {
                return false;
            }

            $currentReserved = (int) $inventory->reserved;
            $updatedStock = (int) $inventory->stock + $quantity;

            $inventory->forceFill([
                'stock'             => $updatedStock,
                'available'         => max(0, $updatedStock - $currentReserved),
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
            $inventory = self::newUnlockedQuery()
                ->whereKey($this->getKey())
                ->lockForUpdate()
                ->first();

            if (! $inventory instanceof self) {
                return false;
            }

            $currentReserved = (int) $inventory->reserved;
            $updatedStock = (int) $inventory->stock - $quantity;

            if ($updatedStock < 0 || $updatedStock < $currentReserved) {
                return false;
            }

            $inventory->forceFill([
                'stock'     => $updatedStock,
                'available' => max(0, $updatedStock - $currentReserved),
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

    /**
     * Order inventories by their associated variant SKU while providing a
     * deterministic secondary ordering.
     */
    public function scopeOrderedByName(Builder $query, string $direction = 'asc'): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        if ($this->resolveNameColumnForOrdering() !== 'sku') {
            return $this->scopeOrderedByNameFromTrait($query, $direction);
        }

        $variantTable = (new ProductVariant)->getTable();
        $skuSelector = ProductVariant::query()
            ->select('sku')
            ->whereColumn("{$variantTable}.id", $this->qualifyColumn('variant_id'))
            ->limit(1);

        return $query
            ->orderBy($skuSelector, $direction)
            ->orderBy($this->qualifyColumn($this->getKeyName()));
    }
}
