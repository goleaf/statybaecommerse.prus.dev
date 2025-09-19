<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * VariantInventory
 *
 * Eloquent model representing the VariantInventory entity for variant stock management.
 *
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $appends
 * @method static \Illuminate\Database\Eloquent\Builder|VariantInventory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantInventory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantInventory query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
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
     * @return BelongsTo
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Handle stockMovements functionality with proper error handling.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stockMovements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StockMovement::class, 'variant_inventory_id');
    }

    /**
     * Handle isLowStock functionality with proper error handling.
     * @return bool
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->available <= $this->reorder_point;
    }

    /**
     * Handle isOutOfStock functionality with proper error handling.
     * @return bool
     */
    public function getIsOutOfStockAttribute(): bool
    {
        return $this->available <= 0;
    }

    /**
     * Handle needsReorder functionality with proper error handling.
     * @return bool
     */
    public function getNeedsReorderAttribute(): bool
    {
        return $this->available <= $this->reorder_point;
    }

    /**
     * Handle getStockStatusAttribute functionality with proper error handling.
     * @return string
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
     * @return float
     */
    public function getUtilizationPercentageAttribute(): float
    {
        if ($this->stock <= 0) {
            return 0.0;
        }

        return ($this->reserved / $this->stock) * 100;
    }

    /**
     * Handle scopeInStock functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeInStock($query)
    {
        return $query->where('available', '>', 0);
    }

    /**
     * Handle scopeLowStock functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('available <= reorder_point');
    }

    /**
     * Handle scopeOutOfStock functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('available', '<=', 0);
    }

    /**
     * Handle scopeNeedsReorder functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeNeedsReorder($query)
    {
        return $query->whereRaw('available <= reorder_point');
    }

    /**
     * Handle scopeByWarehouse functionality with proper error handling.
     * @param mixed $query
     * @param string $warehouseCode
     */
    public function scopeByWarehouse($query, string $warehouseCode)
    {
        return $query->where('warehouse_code', $warehouseCode);
    }

    /**
     * Reserve stock for an order.
     * @param int $quantity
     * @return bool
     */
    public function reserveStock(int $quantity): bool
    {
        if ($this->available < $quantity) {
            return false;
        }

        $this->reserved += $quantity;
        $this->available = $this->stock - $this->reserved;

        return $this->save();
    }

    /**
     * Release reserved stock.
     * @param int $quantity
     * @return bool
     */
    public function releaseStock(int $quantity): bool
    {
        if ($this->reserved < $quantity) {
            return false;
        }

        $this->reserved -= $quantity;
        $this->available = $this->stock - $this->reserved;

        return $this->save();
    }

    /**
     * Add stock to inventory.
     * @param int $quantity
     * @return bool
     */
    public function addStock(int $quantity): bool
    {
        $this->stock += $quantity;
        $this->available = $this->stock - $this->reserved;
        $this->last_restocked_at = now();

        return $this->save();
    }

    /**
     * Remove stock from inventory.
     * @param int $quantity
     * @return bool
     */
    public function removeStock(int $quantity): bool
    {
        if ($this->stock < $quantity) {
            return false;
        }

        $this->stock -= $quantity;
        $this->available = $this->stock - $this->reserved;

        return $this->save();
    }

    /**
     * Update available stock calculation.
     * @return bool
     */
    public function updateAvailableStock(): bool
    {
        $this->available = max(0, $this->stock - $this->reserved);
        return $this->save();
    }

    /**
     * Get stock status badge color.
     * @return string
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
     * @return string
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
