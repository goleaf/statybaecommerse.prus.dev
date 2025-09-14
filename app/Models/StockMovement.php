<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * StockMovement
 * 
 * Eloquent model representing the StockMovement entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement query()
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class StockMovement extends Model
{
    use HasFactory;
    protected $table = 'stock_movements';
    protected $fillable = ['variant_inventory_id', 'quantity', 'type', 'reason', 'reference', 'notes', 'user_id', 'moved_at'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['quantity' => 'integer', 'moved_at' => 'datetime'];
    }
    /**
     * Handle variantInventory functionality with proper error handling.
     * @return BelongsTo
     */
    public function variantInventory(): BelongsTo
    {
        return $this->belongsTo(VariantInventory::class, 'variant_inventory_id');
    }
    /**
     * Handle user functionality with proper error handling.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    /**
     * Handle scopeInbound functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeInbound($query)
    {
        return $query->where('type', 'in');
    }
    /**
     * Handle scopeOutbound functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeOutbound($query)
    {
        return $query->where('type', 'out');
    }
    /**
     * Handle scopeByReason functionality with proper error handling.
     * @param mixed $query
     * @param string $reason
     */
    public function scopeByReason($query, string $reason)
    {
        return $query->where('reason', $reason);
    }
    /**
     * Handle scopeByUser functionality with proper error handling.
     * @param mixed $query
     * @param int $userId
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
    /**
     * Handle scopeRecent functionality with proper error handling.
     * @param mixed $query
     * @param int $days
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('moved_at', '>=', now()->subDays($days));
    }
    /**
     * Handle getTypeLabelAttribute functionality with proper error handling.
     * @return string
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'in' => __('inventory.stock_in'),
            'out' => __('inventory.stock_out'),
            default => __('inventory.unknown'),
        };
    }
    /**
     * Handle getReasonLabelAttribute functionality with proper error handling.
     * @return string
     */
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