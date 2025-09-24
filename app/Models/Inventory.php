<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Inventory
 *
 * Eloquent model representing the Inventory entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventories';

    protected $fillable = ['product_id', 'location_id', 'quantity', 'reserved', 'incoming', 'threshold', 'is_tracked'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['quantity' => 'integer', 'reserved' => 'integer', 'incoming' => 'integer', 'threshold' => 'integer', 'is_tracked' => 'boolean'];
    }

    /**
     * Handle product functionality with proper error handling.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Handle location functionality with proper error handling.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Handle scopeTracked functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeTracked($query)
    {
        return $query->where('is_tracked', true);
    }

    /**
     * Handle scopeLowStock functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity <= threshold');
    }

    /**
     * Handle getAvailableQuantityAttribute functionality with proper error handling.
     */
    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->reserved);
    }

    /**
     * Handle isLowStock functionality with proper error handling.
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->threshold;
    }

    /**
     * Handle isOutOfStock functionality with proper error handling.
     */
    public function isOutOfStock(): bool
    {
        return $this->available_quantity <= 0;
    }
}
