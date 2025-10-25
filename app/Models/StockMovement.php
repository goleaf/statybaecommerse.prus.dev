<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
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
 *
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement query()
 *
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
     */
    protected function casts(): array
    {
        return ['quantity' => 'integer', 'moved_at' => 'datetime'];
    }

    /**
     * Provide access to the owning inventory record for this movement.
     *
     * @return BelongsTo<VariantInventory, self>
     */
    public function variantInventory(): BelongsTo
    {
        return $this->belongsTo(VariantInventory::class, 'variant_inventory_id');
    }

    /**
     * Resolve the user responsible for the stock adjustment when available.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope helper that narrows the query to inbound movements only.
     */
    public function scopeInbound(Builder $query): Builder
    {
        return $query->where('type', 'in');
    }

    /**
     * Scope helper that narrows the query to outbound movements only.
     */
    public function scopeOutbound(Builder $query): Builder
    {
        return $query->where('type', 'out');
    }

    /**
     * Scope helper for retrieving movements filtered by their reason string.
     */
    public function scopeByReason(Builder $query, string $reason): Builder
    {
        return $query->where('reason', $reason);
    }

    /**
     * Scope helper for retrieving movements attributed to a specific user.
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope helper for restricting results to movements within a recent window.
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('moved_at', '>=', now()->subDays($days));
    }

    /**
     * Handle getTypeLabelAttribute functionality with proper error handling.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'in'    => __('inventory.stock_in'),
            'out'   => __('inventory.stock_out'),
            default => __('inventory.unknown'),
        };
    }

    /**
     * Handle getReasonLabelAttribute functionality with proper error handling.
     */
    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            'sale'              => __('inventory.reason_sale'),
            'return'            => __('inventory.reason_return'),
            'adjustment'        => __('inventory.reason_adjustment'),
            'manual_adjustment' => __('inventory.reason_manual_adjustment'),
            'restock'           => __('inventory.reason_restock'),
            'damage'            => __('inventory.reason_damage'),
            'theft'             => __('inventory.reason_theft'),
            'transfer'          => __('inventory.reason_transfer'),
            default             => $this->reason,
        };
    }
}
