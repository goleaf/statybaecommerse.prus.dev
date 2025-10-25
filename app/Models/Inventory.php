<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Explicitly define the table name so future refactors keep factory alignment.
     */
    protected $table = 'inventories';

    /**
     * @var array<int, string>
     */
    protected $fillable = ['product_id', 'location_id', 'quantity', 'reserved', 'incoming', 'threshold', 'is_tracked'];

    /**
     * @var array<int, string>
     */
    protected $appends = ['product_name'];

    /**
     * Provide sensible defaults that avoid null math in stock calculations.
     *
     * @var array<string, int|bool>
     */
    protected $attributes = [
        'quantity'   => 0,
        'reserved'   => 0,
        'incoming'   => 0,
        'threshold'  => 0,
        'is_tracked' => true,
    ];

    /**
     * Handle casts functionality with proper error handling.
     *
     * @return array<string, string>
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
     * @param  Builder<Inventory>  $query
     */
    public function scopeTracked(Builder $query): Builder
    {
        return $query->where('is_tracked', true);
    }

    /**
     * Handle scopeLowStock functionality with proper error handling.
     *
     * @param  Builder<Inventory>  $query
     */
    public function scopeLowStock(Builder $query): Builder
    {
        // Use explicit column comparisons to stay database agnostic while keeping SQL readable.
        return $query
            ->whereColumn('quantity', '>', 'reserved')
            ->whereRaw('(quantity - reserved) <= threshold');
    }

    /**
     * Handle getAvailableQuantityAttribute functionality with proper error handling.
     */
    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->reserved);
    }

    /**
     * Handle getProductNameAttribute functionality with proper error handling.
     */
    public function getProductNameAttribute(): string
    {
        return $this->product?->name ?? '';
    }

    /**
     * Handle isLowStock functionality with proper error handling.
     */
    public function isLowStock(): bool
    {
        $available = $this->available_quantity;
        $threshold = (int) $this->threshold;

        // If the threshold is not a positive integer we treat the stock as not low.
        if ($threshold <= 0) {
            return false;
        }

        if ($available <= 0) {
            return false;
        }

        return $available <= $threshold;
    }

    /**
     * Handle isOutOfStock functionality with proper error handling.
     */
    public function isOutOfStock(): bool
    {
        return $this->available_quantity <= 0;
    }
}
