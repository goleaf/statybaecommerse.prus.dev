<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class VariantInventory extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'variant_inventories';

    protected $fillable = [
        'variant_id',
        'location_id',
        'stock',
        'reserved',
        'incoming',
        'threshold',
        'is_tracked',
        'notes',
        'last_restocked_at',
        'last_sold_at',
        'cost_per_unit',
        'reorder_point',
        'max_stock_level',
        'supplier_id',
        'batch_number',
        'expiry_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'reserved' => 'integer',
            'incoming' => 'integer',
            'threshold' => 'integer',
            'is_tracked' => 'boolean',
            'cost_per_unit' => 'decimal:2',
            'reorder_point' => 'integer',
            'max_stock_level' => 'integer',
            'last_restocked_at' => 'datetime',
            'last_sold_at' => 'datetime',
            'expiry_date' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['stock', 'reserved', 'incoming', 'threshold', 'is_tracked', 'notes', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Variant Inventory {$eventName}")
            ->useLogName('variant_inventory');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'supplier_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'variant_inventory_id');
    }

    public function scopeTracked($query)
    {
        return $query->where('is_tracked', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock <= threshold');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock', '<=', 0);
    }

    public function scopeNeedsReorder($query)
    {
        return $query->whereRaw('stock <= reorder_point');
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays($days));
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByLocation($query, int $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    public function scopeByVariant($query, int $variantId)
    {
        return $query->where('variant_id', $variantId);
    }

    public function getAvailableStockAttribute(): int
    {
        return max(0, $this->stock - $this->reserved);
    }

    public function getStockValueAttribute(): float
    {
        return $this->stock * ($this->cost_per_unit ?? 0);
    }

    public function getReservedValueAttribute(): float
    {
        return $this->reserved * ($this->cost_per_unit ?? 0);
    }

    public function getTotalValueAttribute(): float
    {
        return $this->stock_value + $this->reserved_value;
    }

    public function getStockStatusAttribute(): string
    {
        if (! $this->is_tracked) {
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

    public function isLowStock(): bool
    {
        return $this->stock <= $this->threshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->available_stock <= 0;
    }

    public function needsReorder(): bool
    {
        return $this->stock <= $this->reorder_point;
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date && $this->expiry_date <= now()->addDays($days);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date < now();
    }

    public function canReserve(int $quantity): bool
    {
        return $this->available_stock >= $quantity;
    }

    public function reserve(int $quantity): bool
    {
        if (! $this->canReserve($quantity)) {
            return false;
        }

        $this->increment('reserved', $quantity);

        return true;
    }

    public function unreserve(int $quantity): void
    {
        $this->decrement('reserved', max(0, $quantity));
    }

    public function adjustStock(int $quantity, string $reason = 'manual_adjustment'): void
    {
        $this->increment('stock', $quantity);

        // Log the stock movement
        $this->stockMovements()->create([
            'quantity' => $quantity,
            'type' => $quantity > 0 ? 'in' : 'out',
            'reason' => $reason,
            'reference' => 'manual_adjustment',
            'user_id' => auth()->id(),
            'moved_at' => now(),
        ]);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->variant->display_name.' - '.$this->location->name;
    }

    public function getProductNameAttribute(): string
    {
        return $this->variant->product->name;
    }

    public function getVariantNameAttribute(): string
    {
        return $this->variant->display_name;
    }

    public function getLocationNameAttribute(): string
    {
        return $this->location->name;
    }
}
