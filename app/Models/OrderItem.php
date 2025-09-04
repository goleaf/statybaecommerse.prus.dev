<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'name',
        'sku',
        'quantity',
        'unit_price',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'float',
            'total' => 'float',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (OrderItem $orderItem) {
            if (!$orderItem->total) {
                $orderItem->total = $orderItem->unit_price * $orderItem->quantity;
            }
        });

        static::updating(function (OrderItem $orderItem) {
            if ($orderItem->isDirty(['unit_price', 'quantity'])) {
                $orderItem->total = $orderItem->unit_price * $orderItem->quantity;
            }
        });
    }
}