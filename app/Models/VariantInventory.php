<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\StatusScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
/**
 * VariantInventory
 * 
 * Eloquent model representing the VariantInventory entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|VariantInventory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantInventory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VariantInventory query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, StatusScope::class])]
final class VariantInventory extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;
    protected $table = 'variant_inventories';
    protected $fillable = ['variant_id', 'location_id', 'stock', 'reserved', 'incoming', 'threshold', 'is_tracked', 'notes', 'last_restocked_at', 'last_sold_at', 'cost_per_unit', 'reorder_point', 'max_stock_level', 'supplier_id', 'batch_number', 'expiry_date', 'status'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['stock' => 'integer', 'reserved' => 'integer', 'incoming' => 'integer', 'threshold' => 'integer', 'is_tracked' => 'boolean', 'cost_per_unit' => 'decimal:2', 'reorder_point' => 'integer', 'max_stock_level' => 'integer', 'last_restocked_at' => 'datetime', 'last_sold_at' => 'datetime', 'expiry_date' => 'date'];
    }
    /**
     * Handle getActivitylogOptions functionality with proper error handling.
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['stock', 'reserved', 'incoming', 'threshold', 'is_tracked', 'notes', 'status'])->logOnlyDirty()->dontSubmitEmptyLogs()->setDescriptionForEvent(fn(string $eventName) => "Variant Inventory {$eventName}")->useLogName('variant_inventory');
    }
    /**
     * Handle variant functionality with proper error handling.
     * @return BelongsTo
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
    /**
     * Handle location functionality with proper error handling.
     * @return BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
    /**
     * Handle supplier functionality with proper error handling.
     * @return BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'supplier_id');
    }
    /**
     * Handle stockMovements functionality with proper error handling.
     * @return HasMany
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'variant_inventory_id');
    }
    /**
     * Handle scopeTracked functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeTracked($query)
    {
        return $query->where('is_tracked', true);
    }
    /**
     * Handle scopeLowStock functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock <= threshold');
    }
    /**
     * Handle scopeOutOfStock functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock', '<=', 0);
    }
    /**
     * Handle scopeNeedsReorder functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeNeedsReorder($query)
    {
        return $query->whereRaw('stock <= reorder_point');
    }
    /**
     * Handle scopeExpiringSoon functionality with proper error handling.
     * @param mixed $query
     * @param int $days
     */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')->where('expiry_date', '<=', now()->addDays($days));
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
     * Handle scopeByLocation functionality with proper error handling.
     * @param mixed $query
     * @param int $locationId
     */
    public function scopeByLocation($query, int $locationId)
    {
        return $query->where('location_id', $locationId);
    }
    /**
     * Handle scopeByVariant functionality with proper error handling.
     * @param mixed $query
     * @param int $variantId
     */
    public function scopeByVariant($query, int $variantId)
    {
        return $query->where('variant_id', $variantId);
    }
    /**
     * Handle getAvailableStockAttribute functionality with proper error handling.
     * @return int
     */
    public function getAvailableStockAttribute(): int
    {
        return max(0, $this->stock - $this->reserved);
    }
    /**
     * Handle getStockValueAttribute functionality with proper error handling.
     * @return float
     */
    public function getStockValueAttribute(): float
    {
        return $this->stock * ($this->cost_per_unit ?? 0);
    }
    /**
     * Handle getReservedValueAttribute functionality with proper error handling.
     * @return float
     */
    public function getReservedValueAttribute(): float
    {
        return $this->reserved * ($this->cost_per_unit ?? 0);
    }
    /**
     * Handle getTotalValueAttribute functionality with proper error handling.
     * @return float
     */
    public function getTotalValueAttribute(): float
    {
        return $this->stock_value + $this->reserved_value;
    }
    /**
     * Handle getStockStatusAttribute functionality with proper error handling.
     * @return string
     */
    public function getStockStatusAttribute(): string
    {
        if (!$this->is_tracked) {
            return 'not_tracked';
        }
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        }
        if ($this->isLowStock()) {
            return 'low_stock';
        }
        if ($this->needsReorder()) {
            return 'needs_reorder';
        }
        return 'in_stock';
    }
    /**
     * Handle getStockStatusLabelAttribute functionality with proper error handling.
     * @return string
     */
    public function getStockStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'not_tracked' => __('inventory.not_tracked'),
            'out_of_stock' => __('inventory.out_of_stock'),
            'low_stock' => __('inventory.low_stock'),
            'needs_reorder' => __('inventory.needs_reorder'),
            'in_stock' => __('inventory.in_stock'),
            default => __('inventory.unknown'),
        };
    }
    /**
     * Handle isLowStock functionality with proper error handling.
     * @return bool
     */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->threshold;
    }
    /**
     * Handle isOutOfStock functionality with proper error handling.
     * @return bool
     */
    public function isOutOfStock(): bool
    {
        return $this->available_stock <= 0;
    }
    /**
     * Handle needsReorder functionality with proper error handling.
     * @return bool
     */
    public function needsReorder(): bool
    {
        return $this->stock <= $this->reorder_point;
    }
    /**
     * Handle isExpiringSoon functionality with proper error handling.
     * @param int $days
     * @return bool
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date && $this->expiry_date <= now()->addDays($days);
    }
    /**
     * Handle isExpired functionality with proper error handling.
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date < now();
    }
    /**
     * Handle canReserve functionality with proper error handling.
     * @param int $quantity
     * @return bool
     */
    public function canReserve(int $quantity): bool
    {
        return $this->available_stock >= $quantity;
    }
    /**
     * Handle reserve functionality with proper error handling.
     * @param int $quantity
     * @return bool
     */
    public function reserve(int $quantity): bool
    {
        if (!$this->canReserve($quantity)) {
            return false;
        }
        $this->increment('reserved', $quantity);
        return true;
    }
    /**
     * Handle unreserve functionality with proper error handling.
     * @param int $quantity
     * @return void
     */
    public function unreserve(int $quantity): void
    {
        $this->decrement('reserved', max(0, $quantity));
    }
    /**
     * Handle adjustStock functionality with proper error handling.
     * @param int $quantity
     * @param string $reason
     * @return void
     */
    public function adjustStock(int $quantity, string $reason = 'manual_adjustment'): void
    {
        $this->increment('stock', $quantity);
        // Log the stock movement
        $this->stockMovements()->create(['quantity' => $quantity, 'type' => $quantity > 0 ? 'in' : 'out', 'reason' => $reason, 'reference' => 'manual_adjustment', 'user_id' => auth()->id(), 'moved_at' => now()]);
    }
    /**
     * Handle getDisplayNameAttribute functionality with proper error handling.
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->variant->display_name . ' - ' . $this->location->name;
    }
    /**
     * Handle getProductNameAttribute functionality with proper error handling.
     * @return string
     */
    public function getProductNameAttribute(): string
    {
        return $this->variant->product->name;
    }
    /**
     * Handle getVariantNameAttribute functionality with proper error handling.
     * @return string
     */
    public function getVariantNameAttribute(): string
    {
        return $this->variant->display_name;
    }
    /**
     * Handle getLocationNameAttribute functionality with proper error handling.
     * @return string
     */
    public function getLocationNameAttribute(): string
    {
        return $this->location->name;
    }
}