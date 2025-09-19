<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

/**
 * OrderItem
 *
 * Eloquent model representing the OrderItem entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem query()
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';
    protected $fillable = ['order_id', 'product_id', 'product_variant_id', 'name', 'sku', 'quantity', 'unit_price', 'price', 'total', 'notes'];

    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['quantity' => 'integer', 'unit_price' => 'float', 'price' => 'float', 'total' => 'float'];
    }

    /**
     * Handle order functionality with proper error handling.
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Handle product functionality with proper error handling.
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Handle productVariant functionality with proper error handling.
     * @return BelongsTo
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Boot the service provider or trait functionality.
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();
        self::creating(function (OrderItem $orderItem) {
            if (isset($orderItem->price) && empty($orderItem->unit_price)) {
                $orderItem->unit_price = $orderItem->price;
            }
            if (!$orderItem->total) {
                $orderItem->total = $orderItem->unit_price * $orderItem->quantity;
            }
        });
        self::updating(function (OrderItem $orderItem) {
            if ($orderItem->isDirty(['unit_price', 'quantity'])) {
                $orderItem->total = $orderItem->unit_price * $orderItem->quantity;
            }
            if ($orderItem->isDirty('price') && !$orderItem->isDirty('unit_price')) {
                $orderItem->unit_price = $orderItem->price;
            }
        });
    }
}
