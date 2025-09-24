<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OrderItem
 *
 * Eloquent model representing the OrderItem entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    protected $fillable = ['order_id', 'product_id', 'product_variant_id', 'name', 'sku', 'quantity', 'unit_price', 'price', 'total', 'notes', 'discount_amount', 'status'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'float',
            'price' => 'float',
            'total' => 'float',
            'discount_amount' => 'float',
        ];
    }

    /**
     * Handle order functionality with proper error handling.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Handle product functionality with proper error handling.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Handle productVariant functionality with proper error handling.
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Boot the service provider or trait functionality.
     */
    protected static function boot(): void
    {
        parent::boot();
        self::creating(function (OrderItem $orderItem) {
            if (isset($orderItem->price) && empty($orderItem->unit_price)) {
                $orderItem->unit_price = $orderItem->price;
            }
            $discount = (float) ($orderItem->discount_amount ?? 0);
            if (! $orderItem->total) {
                $orderItem->total = ($orderItem->unit_price * $orderItem->quantity) - $discount;
            }
        });
        self::updating(function (OrderItem $orderItem) {
            if ($orderItem->isDirty(['unit_price', 'quantity', 'discount_amount'])) {
                $discount = (float) ($orderItem->discount_amount ?? 0);
                $orderItem->total = ($orderItem->unit_price * $orderItem->quantity) - $discount;
            }
            if ($orderItem->isDirty('price') && ! $orderItem->isDirty('unit_price')) {
                $orderItem->unit_price = $orderItem->price;
            }
        });
    }
}
