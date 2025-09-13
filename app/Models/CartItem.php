<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class CartItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'session_id',
        'user_id',
        'product_id',
        'variant_id',
        'product_variant_id',
        'quantity',
        'minimum_quantity',
        'unit_price',
        'total_price',
        'price',
        'product_snapshot',
        'notes',
        'attributes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'minimum_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'price' => 'decimal:2',
        'product_snapshot' => 'array',
        'attributes' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function updateTotalPrice(): void
    {
        $this->total_price = $this->unit_price * $this->quantity;
        $this->save();
    }

    public function needsRestocking(): bool
    {
        return $this->quantity < $this->minimum_quantity;
    }

    public function getMinimumQuantity(): int
    {
        return $this->minimum_quantity ?? 1;
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return app_money_format($this->total_price);
    }

    public function getFormattedUnitPriceAttribute(): string
    {
        return app_money_format($this->unit_price);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->calculateSubtotal();
    }

    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function updateQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
        $this->updateTotalPrice();
    }

    public function incrementQuantity(int $amount = 1): void
    {
        $this->quantity += $amount;
        $this->updateTotalPrice();
    }

    public function decrementQuantity(int $amount = 1): void
    {
        $this->quantity = max(0, $this->quantity - $amount);
        if ($this->quantity === 0) {
            $this->forceDelete();
        } else {
            $this->updateTotalPrice();
        }
    }

    public function calculateSubtotal(): float
    {
        $price = $this->price ?? $this->unit_price;
        return $price * $this->quantity;
    }
}
