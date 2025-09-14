<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final /**
 * StockMovement
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class StockMovement extends Model
{
    use HasFactory;

    protected $table = 'stock_movements';

    protected $fillable = [
        'variant_inventory_id',
        'quantity',
        'type',
        'reason',
        'reference',
        'notes',
        'user_id',
        'moved_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'moved_at' => 'datetime',
        ];
    }

    public function variantInventory(): BelongsTo
    {
        return $this->belongsTo(VariantInventory::class, 'variant_inventory_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeInbound($query)
    {
        return $query->where('type', 'in');
    }

    public function scopeOutbound($query)
    {
        return $query->where('type', 'out');
    }

    public function scopeByReason($query, string $reason)
    {
        return $query->where('reason', $reason);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('moved_at', '>=', now()->subDays($days));
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'in' => __('inventory.stock_in'),
            'out' => __('inventory.stock_out'),
            default => __('inventory.unknown'),
        };
    }

    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            'sale' => __('inventory.reason_sale'),
            'return' => __('inventory.reason_return'),
            'adjustment' => __('inventory.reason_adjustment'),
            'manual_adjustment' => __('inventory.reason_manual_adjustment'),
            'restock' => __('inventory.reason_restock'),
            'damage' => __('inventory.reason_damage'),
            'theft' => __('inventory.reason_theft'),
            'transfer' => __('inventory.reason_transfer'),
            default => $this->reason,
        };
    }
}
