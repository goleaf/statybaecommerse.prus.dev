<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * OrderItem
 *
 * Eloquent model representing the OrderItem entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property int                             $id
 * @property int                             $order_id
 * @property int                             $product_id
 * @property int|null                        $product_variant_id
 * @property string                          $name
 * @property string|null                     $sku
 * @property int                             $quantity
 * @property float                           $unit_price
 * @property float|null                      $price
 * @property float                           $total
 * @property float|null                      $discount_amount
 * @property string|null                     $status
 * @property string|null                     $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem query()
 * @method static OrderItemFactory                                factory($count = null, $state = [])
 *
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    protected $table = 'order_items';

    protected $fillable = ['order_id', 'product_id', 'product_variant_id', 'name', 'sku', 'quantity', 'unit_price', 'price', 'total', 'notes', 'discount_amount', 'status'];

    /**
     * Scope the query to always order items by their display name.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeOrderedByName(Builder $query): Builder
    {
        // Ensures consistent alphabetical ordering when presenting order line items.
        return $query->orderBy('name');
    }

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return [
            'quantity'        => 'integer',
            'unit_price'      => 'float',
            'price'           => 'float',
            'total'           => 'float',
            'discount_amount' => 'float',
        ];
    }

    /**
     * Handle order functionality with proper error handling.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Handle product functionality with proper error handling.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Handle productVariant functionality with proper error handling.
     *
     * @return BelongsTo<ProductVariant, $this>
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
        self::creating(function (OrderItem $orderItem): void {
            $product = null;
            $variant = null;

            if ($orderItem->product_id) {
                $product = $orderItem->relationLoaded('product')
                    ? $orderItem->product
                    : Product::query()->find($orderItem->product_id);
            }

            if ($orderItem->product_variant_id) {
                $variant = $orderItem->relationLoaded('productVariant')
                    ? $orderItem->productVariant
                    : ProductVariant::query()->find($orderItem->product_variant_id);
            }

            if (! isset($orderItem->name) && $product !== null) {
                if ($variant !== null && isset($variant->name)) {
                    $productName = $product->name ?? '';
                    $variantName = $variant->name ?? '';
                    $orderItem->name = Str::of($productName)->append(' - ', $variantName)->toString();
                } else {
                    $orderItem->name = $product->name ?? '';
                }
            }

            if (! isset($orderItem->sku)) {
                if ($variant !== null && isset($variant->sku)) {
                    $orderItem->sku = $variant->sku;
                } elseif ($product !== null && isset($product->sku)) {
                    $orderItem->sku = $product->sku;
                }
            }

            if (isset($orderItem->price) && ! isset($orderItem->unit_price)) {
                $orderItem->unit_price = $orderItem->price;
            }
            $discount = (float) ($orderItem->discount_amount ?? 0);
            if (! isset($orderItem->total)) {
                $orderItem->total = ($orderItem->unit_price * $orderItem->quantity) - $discount;
            }
        });
        self::updating(function (OrderItem $orderItem): void {
            if ($orderItem->isDirty(['unit_price', 'quantity', 'discount_amount']) && ! $orderItem->isDirty('total')) {
                $discount = (float) ($orderItem->discount_amount ?? 0);
                $orderItem->total = ($orderItem->unit_price * $orderItem->quantity) - $discount;
            }
            if ($orderItem->isDirty('price') && ! $orderItem->isDirty('unit_price') && $orderItem->price !== null) {
                $orderItem->unit_price = $orderItem->price;
            }
        });
    }
}
